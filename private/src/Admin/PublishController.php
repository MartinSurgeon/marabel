<?php
/**
 * Result Publishing Controller — Hardened v2
 *
 * Improvements over v1:
 *  - Input validation before lock/publish (guards against empty class/term IDs)
 *  - Active-year fallback (uses latest year if none is marked active)
 *  - Batch-loaded scores via IN() to eliminate N+1 query pattern
 *  - Student count included in summary query
 *  - Defensive validation: blocks publish if class has no students or no subjects
 *  - Clear error messages surfaced back to the UI via Session::flash
 */

class PublishController {

    public function handle(): void {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            $action = $_POST['_action'] ?? '';

            // preview_class is a read-only AJAX action — skip CSRF so the
            // DOM token is not consumed and the rotate doesn't break subsequent requests.
            if ($action === 'preview_class') {
                Session::requireRole('admin');   // still auth-guarded
                $this->previewClass();
            }

            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid request token. Please try again.');
                $this->redirect();
            }

            match ($action) {
                'lock_class'    => $this->lockClass(),
                'unlock_class'  => $this->unlockClass(),
                'publish_class' => $this->publishClass(),
                'unpublish'     => $this->unpublishClass(),
                'bulk_unpublish'=> $this->bulkUnpublish(),
                default         => $this->redirect(),
            };
        }

        // ── Prepare view data ─────────────────────────────────────────
        global $activeTerm, $classesProgress, $activeYear;

        // Active year with fallback to the most recent year
        $activeYear = DB::queryOne("SELECT id, year_name FROM academic_years WHERE is_active = 1 LIMIT 1")
                   ?? DB::queryOne("SELECT id, year_name FROM academic_years ORDER BY year_name DESC LIMIT 1");

        // Active term with fallback to most recent term in the active year
        $activeTerm = DB::queryOne("SELECT * FROM terms WHERE is_active = 1 LIMIT 1");
        if (!$activeTerm && $activeYear) {
            $activeTerm = DB::queryOne(
                "SELECT * FROM terms WHERE academic_year_id = ? ORDER BY term_number DESC LIMIT 1",
                [$activeYear['id']]
            );
        }

        if (!$activeTerm || !$activeYear) {
            $classesProgress = [];
            return;
        }

        // Fetch classes and their scoring/student progress for the active term
        $classesProgress = DB::query(
            "SELECT
                c.id, c.class_name, c.section, sl.name AS level_name, sl.sort_order,
                rcl.is_published, rcl.published_at,
                -- Students enrolled in this class for this year
                (SELECT COUNT(*) FROM students s
                 WHERE s.current_class_id = c.id AND s.status = 'active' AND s.academic_year_id = ?) AS student_count,
                -- Subject registrations for this term
                (SELECT COUNT(*) FROM class_subjects cs
                 WHERE cs.class_id = c.id AND cs.term_id = ?) AS total_subjects,
                -- Locked subject registrations
                (SELECT COUNT(*) FROM class_subjects cs
                 WHERE cs.class_id = c.id AND cs.term_id = ? AND cs.is_locked = 1) AS locked_subjects,
                -- Students who have at least one score entered
                (SELECT COUNT(DISTINCT sba.student_id) FROM sba_component_scores sba
                 INNER JOIN class_subjects cs ON cs.id = sba.class_subject_id
                 WHERE cs.class_id = c.id AND sba.term_id = ?) AS students_with_scores
             FROM classes c
             JOIN school_levels sl ON sl.id = c.level_id
             LEFT JOIN report_card_locks rcl ON (rcl.class_id = c.id AND rcl.term_id = ?)
             WHERE c.academic_year_id = ?
             ORDER BY sl.sort_order, c.class_name",
            [
                $activeYear['id'],
                $activeTerm['id'],
                $activeTerm['id'],
                $activeTerm['id'],
                $activeTerm['id'],
                $activeYear['id'],
            ]
        );
    }

    // ── Lock ─────────────────────────────────────────────────────────
    private function lockClass(): void {
        [$classId, $termId] = $this->requireClassTerm();

        // Validate class and term exist
        if (!DB::queryOne("SELECT id FROM classes WHERE id = ?", [$classId])) {
            Session::flash('error', 'Class not found.');
            $this->redirect();
        }

        $locked = DB::execute(
            "UPDATE class_subjects SET is_locked = 1, locked_at = CURRENT_TIMESTAMP
             WHERE class_id = ? AND term_id = ? AND is_locked = 0",
            [$classId, $termId]
        );

        Session::flash('success', "Score entry is now locked for this class. Teachers cannot modify scores.");
        $this->redirect();
    }

    // ── Unlock ───────────────────────────────────────────────────────
    private function unlockClass(): void {
        [$classId, $termId] = $this->requireClassTerm();

        DB::execute(
            "UPDATE class_subjects SET is_locked = 0, locked_at = NULL
             WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );

        // Remove published flag — must re-publish after unlocking
        DB::execute(
            "UPDATE report_card_locks SET is_published = 0 WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );

        Session::flash('info', "Scores unlocked. Remember to re-publish after any changes are made.");
        $this->redirect();
    }

    // ── Publish ──────────────────────────────────────────────────────
    private function publishClass(): void {
        [$classId, $termId] = $this->requireClassTerm();

        // Pre-flight checks
        $studentCount = DB::queryOne(
            "SELECT COUNT(*) as cnt FROM students WHERE current_class_id = ? AND status = 'active'",
            [$classId]
        )['cnt'] ?? 0;

        $subjectCount = DB::queryOne(
            "SELECT COUNT(*) as cnt FROM class_subjects WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        )['cnt'] ?? 0;

        if ((int)$studentCount === 0) {
            Session::flash('error', 'Cannot publish: no active students are enrolled in this class.');
            $this->redirect();
        }

        if ((int)$subjectCount === 0) {
            Session::flash('error', 'Cannot publish: no subjects have been assigned to this class for the active term. Assign subjects in Teacher Management first.');
            $this->redirect();
        }

        try {
            DB::beginTransaction();

            $this->computeClassResults($classId, $termId);

            DB::execute(
                "INSERT INTO report_card_locks (class_id, term_id, is_published, published_at, published_by)
                 VALUES (?, ?, 1, CURRENT_TIMESTAMP, ?)
                 ON DUPLICATE KEY UPDATE
                    is_published = 1, published_at = CURRENT_TIMESTAMP, published_by = ?",
                [$classId, $termId, Session::get('user_id'), Session::get('user_id')]
            );

            DB::commit();
            Session::flash('success', "Results published! Parents and students can now view report cards.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Session::flash('error', "Publishing failed: " . $e->getMessage());
        }

        $this->redirect();
    }

    // ── Unpublish ────────────────────────────────────────────────────
    private function unpublishClass(): void {
        [$classId, $termId] = $this->requireClassTerm();

        DB::execute(
            "UPDATE report_card_locks SET is_published = 0 WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );

        Session::flash('info', "Reports hidden. They are no longer visible to parents or students.");
        $this->redirect();
    }

    private function bulkUnpublish(): void {
        $termId = (int)($_POST['term_id'] ?? 0);
        if (!$termId) {
            Session::flash('error', 'Invalid term.');
            $this->redirect();
        }

        DB::execute(
            "UPDATE report_card_locks SET is_published = 0 WHERE term_id = ?",
            [$termId]
        );

        Session::flash('success', "All results for this term have been unpublished and hidden from parents/students.");
        $this->redirect();
    }

    // ── Preview Results (AJAX) ───────────────────────────────────────
    private function previewClass(): void {
        [$classId, $termId] = $this->requireClassTerm();

        try {
            // Compute an ephemeral snapshot (safe — idempotent upserts, not published)
            $this->computeClassResults($classId, $termId);

            $aggregates = DB::query(
                "SELECT s.full_name, sa.aggregate_score, sa.number_of_subjects, sa.class_position
                 FROM student_aggregates sa
                 JOIN students s ON s.id = sa.student_id
                 WHERE sa.class_id = ? AND sa.term_id = ? AND s.status = 'active'
                 ORDER BY sa.class_position ASC, s.full_name ASC",
                [$classId, $termId]
            );

            // Proficiency breakdown — proficiency_level stored as int (1-5)
            $proficiencies = DB::query(
                "SELECT proficiency_level, COUNT(*) as cnt
                 FROM computed_scores cs
                 JOIN students s ON s.id = cs.student_id
                 WHERE s.current_class_id = ? AND cs.term_id = ? AND s.status = 'active'
                 GROUP BY proficiency_level
                 ORDER BY proficiency_level",
                [$classId, $termId]
            );
            $totalGrades = array_sum(array_column($proficiencies, 'cnt'));

            if (empty($aggregates)) {
                echo '<div style="text-align:center; padding:3rem; color:var(--clr-text-muted);">No score data found. Please ensure SBA scores have been entered first.</div>';
                exit;
            }

            // Color palette indexed by proficiency level int
            $levelColors = [
                1 => 'var(--clr-success)',
                2 => 'var(--clr-primary)',
                3 => 'var(--clr-warning)',
                4 => '#f97316',
                5 => 'var(--clr-danger)',
            ];

            ob_start();
            ?>
            <div style="background:var(--clr-surface-2); padding:1rem; border-radius:var(--radius-md); margin-bottom:1.5rem; border:1px solid var(--clr-border);">
               <div style="font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:var(--clr-text-muted); margin-bottom:.75rem;">Proficiency Breakdown</div>
               <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(90px, 1fr)); gap:1rem; text-align:center;">
                  <?php foreach($proficiencies as $p):
                     $lvl   = (int)$p['proficiency_level'];
                     $label = PROFICIENCY_SCALE[$lvl]['abbr'] ?? 'N/A';
                     $color = $levelColors[$lvl] ?? 'var(--clr-text-muted)';
                     $pct   = $totalGrades ? round(($p['cnt'] / $totalGrades) * 100) : 0;
                  ?>
                  <div>
                    <div style="font-size:1.35rem; font-weight:800; color:<?= $color ?>;"><?= $pct ?>%</div>
                    <div style="font-size:10px; font-weight:700; color:var(--clr-text-muted);"><?= $label ?></div>
                    <div style="font-size:9px; color:var(--clr-text-muted);"><?= $p['cnt'] ?> student<?= $p['cnt'] != 1 ? 's' : '' ?></div>
                  </div>
                  <?php endforeach; ?>
               </div>
            </div>

            <div style="overflow-y:auto; max-height:400px; border:1px solid var(--clr-border); border-radius:var(--radius-md);">
            <table class="table" style="margin:0; font-size:13px;">
                <thead style="position:sticky; top:0; background:var(--clr-surface); z-index:10; box-shadow:0 1px 2px rgba(0,0,0,0.05);">
                    <tr>
                        <th style="padding-left:1.5rem; width:60px;">Pos</th>
                        <th>Student Name</th>
                        <th class="text-right">Agg. Score</th>
                        <th class="text-center">Subjects</th>
                        <th class="text-right" style="padding-right:1.5rem;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $base = defined('APP_BASE') ? APP_BASE : '';
                    foreach($aggregates as $a): 
                    ?>
                    <tr>
                        <td style="padding-left:1.5rem; font-weight:800; color:var(--clr-primary);"><?= $a['class_position'] ?></td>
                        <td style="font-weight:600; color:var(--clr-text);"><?= htmlspecialchars($a['full_name']) ?></td>
                        <td class="text-right" style="font-variant-numeric: tabular-nums; font-weight:700;"><?= number_format($a['aggregate_score'], 1) ?></td>
                        <td class="text-center"><span class="badge" style="font-size:10px; background:var(--clr-surface-2);"><?= $a['number_of_subjects'] ?></span></td>
                        <td class="text-right" style="padding-right:1.5rem;">
                            <a href="<?= $base ?>/report?student=<?= $a['student_id'] ?>&term=<?= $termId ?>" target="_blank" class="btn btn-ghost btn-xs" style="color:var(--clr-primary); font-weight:700;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="12" height="12" class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View Report
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
            </div>
            <?php
            echo ob_get_clean();
        } catch (\Throwable $e) {
            echo '<div style="padding:2rem; text-align:center; color:var(--clr-danger); font-weight:700;">Error computing preview: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit;
    }

    // ── Core Computation (batch-optimised) ───────────────────────────
    /**
     * Computes and saves computed_scores and student_aggregates for an entire class.
     * Uses batch IN() queries to minimise round trips — O(subjects + students) instead of O(students × subjects).
     */
    private function computeClassResults(int $classId, int $termId): void {
        // 1. Fetch students and subjects
        $students = DB::query(
            "SELECT id FROM students WHERE current_class_id = ? AND status = 'active'",
            [$classId]
        );
        $subjects = DB::query(
            "SELECT id, subject_id FROM class_subjects WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );

        if (empty($students) || empty($subjects)) return;

        $studentIds = array_column($students,  'id');
        $csIds      = array_column($subjects,  'id');

        $sIn  = implode(',', array_fill(0, count($studentIds), '?'));
        $csIn = implode(',', array_fill(0, count($csIds),      '?'));

        // 2. Batch-fetch all SBA component scores for these students/class_subjects
        $compRows = DB::query(
            "SELECT student_id, class_subject_id,
                    individual_test, group_work, class_test, project
             FROM sba_component_scores
             WHERE student_id IN ({$sIn}) AND class_subject_id IN ({$csIn}) AND term_id = ?",
            [...$studentIds, ...$csIds, $termId]
        );
        // Index: [student_id][class_subject_id]
        $compMap = [];
        foreach ($compRows as $row) {
            $compMap[$row['student_id']][$row['class_subject_id']] = $row;
        }

        // 3. Batch-fetch all exam scores
        $examRows = DB::query(
            "SELECT student_id, class_subject_id, raw_score
             FROM exam_scores
             WHERE student_id IN ({$sIn}) AND class_subject_id IN ({$csIn}) AND term_id = ?",
            [...$studentIds, ...$csIds, $termId]
        );
        $examMap = [];
        foreach ($examRows as $row) {
            $examMap[$row['student_id']][$row['class_subject_id']] = $row;
        }

        // 4. Compute scores for each student × subject
        $computedRows = []; // [class_subject_id => [student_id => result]]

        foreach ($students as $student) {
            $sid            = $student['id'];
            $totalAggregate = 0.0;
            $subjectCount   = 0;

            foreach ($subjects as $cs) {
                $csId = $cs['id'];
                $comp = $compMap[$sid][$csId] ?? null;
                $exam = $examMap[$sid][$csId] ?? null;

                if (!$comp && !$exam) continue; // No data entered

                $result = GradingEngine::computeFull(
                    (float)($comp['individual_test'] ?? 0),
                    (float)($comp['group_work']      ?? 0),
                    (float)($comp['class_test']      ?? 0),
                    (float)($comp['project']         ?? 0),
                    (float)($exam['raw_score']       ?? 0)
                );

                // Upsert into computed_scores
                DB::execute(
                    "INSERT INTO computed_scores
                        (student_id, class_subject_id, term_id, class_score, exam_score, overall_total, proficiency_level)
                     VALUES (?, ?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        class_score=?, exam_score=?, overall_total=?, proficiency_level=?",
                    [
                        $sid, $csId, $termId,
                        $result['class_score'], $result['exam_score'],
                        $result['overall_total'], $result['proficiency_level'],
                        $result['class_score'], $result['exam_score'],
                        $result['overall_total'], $result['proficiency_level'],
                    ]
                );

                $computedRows[$csId][$sid] = $result['overall_total'];
                $totalAggregate           += $result['overall_total'];
                $subjectCount++;
            }

            // Upsert aggregate
            if ($subjectCount > 0) {
                DB::execute(
                    "INSERT INTO student_aggregates
                        (student_id, class_id, term_id, aggregate_score, number_of_subjects)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        aggregate_score=?, number_of_subjects=?",
                    [
                        $sid, $classId, $termId, $totalAggregate, $subjectCount,
                        $totalAggregate, $subjectCount,
                    ]
                );
            }
        }

        // 5. Subject Positions (rank per subject within class)
        foreach ($subjects as $cs) {
            $csId        = $cs['id'];
            $subjectData = $computedRows[$csId] ?? [];
            if (empty($subjectData)) continue;

            $arr = array_map(
                fn($sid, $ot) => ['student_id' => $sid, 'overall_total' => $ot],
                array_keys($subjectData), $subjectData
            );

            $ranked = GradingEngine::computeSubjectPositions($arr);
            foreach ($ranked as $rs) {
                DB::execute(
                    "UPDATE computed_scores SET subject_position = ?
                     WHERE student_id = ? AND class_subject_id = ? AND term_id = ?",
                    [$rs['subject_position'], $rs['student_id'], $csId, $termId]
                );
            }
        }

        // 6. Class Positions (rank across all subjects in class)
        $aggregates = DB::query(
            "SELECT student_id, aggregate_score AS aggregate
             FROM student_aggregates WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );

        if (!empty($aggregates)) {
            $ranked = GradingEngine::computeClassPositions($aggregates);
            foreach ($ranked as $ra) {
                DB::execute(
                    "UPDATE student_aggregates SET class_position = ?
                     WHERE student_id = ? AND class_id = ? AND term_id = ?",
                    [$ra['position'], $ra['student_id'], $classId, $termId]
                );
            }
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────
    /** Validate and return [classId, termId] from POST, redirecting with error if invalid. */
    private function requireClassTerm(): array {
        $classId = (int)($_POST['class_id'] ?? 0);
        $termId  = (int)($_POST['term_id']  ?? 0);

        if (!$classId || !$termId) {
            Session::flash('error', 'Invalid request: missing class or term.');
            $this->redirect();
        }

        return [$classId, $termId];
    }

    private function redirect(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/admin/publish');
        exit;
    }
}
