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
            $className = DB::queryValue("SELECT class_name FROM classes WHERE id = ?", [$classId]);
            Notification::send(null, "Results Published", "Report cards for {$className} have been published.", 'success', '/admin/publish');
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

        $className = DB::queryValue("SELECT class_name FROM classes WHERE id = ?", [$classId]);
        Notification::send(null, "Results Unpublished", "Report cards for {$className} have been hidden.", 'info', '/admin/publish');
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

        Notification::send(null, "Bulk Results Hidden", "All results for the term have been unpublished.", 'warning', '/admin/publish');
        Session::flash('success', "All results for this term have been unpublished and hidden from parents/students.");
        $this->redirect();
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
