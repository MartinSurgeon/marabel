 <?php
/**
 * Promotion Controller
 * Handles student promotion between academic years.
 * Supports BOTH automated (score-threshold) and manual override.
 */

class PromotionController {

    public function handle(): void {
        // ── AJAX: load students for manual override modal ─────────────
        if (isset($_GET['ajax_students']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
            $this->ajaxStudentList();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid request token.');
                $this->redirect();
            }
            $action = $_POST['_action'] ?? '';
            match ($action) {
                'auto_promote'      => $this->autoPromote(),
                'manual_promote'    => $this->manualPromote(),
                'unpromote_class'   => $this->unpromoteClass(),
                default             => $this->redirect(),
            };
        }

        // ── Prepare view data ─────────────────────────────────────────
        global $pageYear, $yearsList, $classesSummary, $nextYearsList, $targetYearClasses;

        $yearsList = DB::query("SELECT id, year_name FROM academic_years ORDER BY year_name DESC");

        // Pick the year being reviewed (defaults to active, then most recent)
        $filterYearId = (int)($_GET['year_id'] ?? 0);
        if (!$filterYearId) {
            $active = DB::queryOne("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1")
                   ?? DB::queryOne("SELECT id FROM academic_years ORDER BY year_name DESC LIMIT 1");
            $filterYearId = (int)($active['id'] ?? 0);
        }

        $pageYear = DB::queryOne("SELECT * FROM academic_years WHERE id = ?", [$filterYearId]);

        // All years except the current one — these are the promotion targets
        $nextYearsList = DB::query(
            "SELECT id, year_name FROM academic_years WHERE id != ? ORDER BY year_name DESC",
            [$filterYearId]
        );
        // Build lookup of classes in each target year (for smart next-class dropdown)
        $targetYearClasses = [];
        foreach ($nextYearsList as $ny) {
            $targetYearClasses[$ny['id']] = DB::query(
                "SELECT c.id, c.class_name, c.section, sl.sort_order as level_sort
                 FROM classes c
                 JOIN school_levels sl ON sl.id = c.level_id
                 WHERE c.academic_year_id = ?
                 ORDER BY sl.sort_order, c.class_name, c.section",
                [$ny['id']]
            );
        }

        // Summarise each class: total students, auto-promotable, needs manual review
        if ($filterYearId) {
            $role = Session::role();
            $uid  = Session::userId();
            $where = "WHERE c.academic_year_id = ?";
            $params = [$filterYearId];

            if ($role === 'teacher') {
                $where .= " AND c.id IN (SELECT class_id FROM class_teachers WHERE teacher_id = ?)";
                $params[] = $uid;
            }

            $classesSummary = DB::query(
                "SELECT
                    c.id, c.class_name, c.section, sl.name as level_name, sl.code as level_code,
                    CASE
                        WHEN c.class_name = 'BASIC 1' THEN 'BASIC 2'
                        WHEN c.class_name = 'BASIC 2' THEN 'BASIC 3'
                        WHEN c.class_name = 'BASIC 3' THEN 'BASIC 4'
                        WHEN c.class_name = 'BASIC 4' THEN 'BASIC 5'
                        WHEN c.class_name = 'BASIC 5' THEN 'BASIC 6'
                        WHEN c.class_name = 'BASIC 6' THEN 'BASIC 7'
                        WHEN c.class_name = 'BASIC 7' THEN 'BASIC 8'
                        WHEN c.class_name = 'BASIC 8' THEN 'BASIC 9'
                        ELSE 'Graduated'
                    END as expected_next_class
                 FROM classes c
                 JOIN school_levels sl ON sl.id = c.level_id
                 $where
                 ORDER BY sl.sort_order, c.class_name",
                $params
            );

            foreach ($classesSummary as &$csItem) {
                $cid = (int)$csItem['id'];
                $expNext = $csItem['expected_next_class'];
                $cName = $csItem['class_name'];

                $counts = DB::queryOne("
                    SELECT 
                        COUNT(DISTINCT s.id) as total_students,
                        COUNT(DISTINCT CASE WHEN sp.promotion_status = 'promoted' THEN s.id END) as promoted_count,
                        COUNT(DISTINCT CASE WHEN sp.promotion_status = 'repeated' THEN s.id END) as repeated_count
                    FROM students s
                    LEFT JOIN student_aggregates sa ON sa.student_id = s.id AND sa.class_id = ?
                    LEFT JOIN class_subjects csb ON csb.class_id = ?
                    LEFT JOIN computed_scores cs ON cs.student_id = s.id AND cs.class_subject_id = csb.id
                    LEFT JOIN student_promotions sp ON sp.student_id = s.id AND sp.academic_year_id = ?
                    WHERE (s.current_class_id = ? AND s.academic_year_id = ?)
                       OR sa.student_id IS NOT NULL
                       OR cs.student_id IS NOT NULL
                       OR (sp.academic_year_id = ? AND (sp.next_class_name = ? OR (sp.promotion_status = 'repeated' AND sp.next_class_name = ?)))
                ", [$cid, $cid, $filterYearId, $cid, $filterYearId, $filterYearId, $expNext, $cName]);

                $tot = (int)($counts['total_students'] ?? 0);
                $pro = (int)($counts['promoted_count'] ?? 0);
                $rep = (int)($counts['repeated_count'] ?? 0);

                $csItem['total_students'] = $tot;
                $csItem['promoted_count'] = $pro;
                $csItem['repeated_count'] = $rep;
                $csItem['pending_count']  = max(0, $tot - ($pro + $rep));
            }
        } else {
            $classesSummary = [];
        }
    }

    /**
     * Auto-promote all students in a class based on aggregate score threshold.
     * Any student scoring >= threshold is promoted, others are held back.
     */
    private function autoPromote(): void {
        $classId     = (int)($_POST['class_id']      ?? 0);
        $yearId      = (int)($_POST['year_id']        ?? 0);
        $nextYearId  = (int)($_POST['next_year_id']   ?? 0);
        $nextClass   = trim($_POST['next_class_name'] ?? '');
        $threshold   = (float)($_POST['threshold']    ?? 50.0);

        if (!$classId || !$yearId || !$nextYearId) {
            Session::flash('error', 'Missing required fields for promotion.');
            $this->redirect($yearId);
        }

        // Security check for teachers
        if (Session::role() === 'teacher') {
            $assigned = DB::queryOne("SELECT 1 FROM class_teachers WHERE class_id = ? AND teacher_id = ?", [$classId, Session::userId()]);
            if (!$assigned) {
                Session::flash('error', 'Access denied: You are not assigned as a Class Teacher for this class.');
                $this->redirect($yearId);
            }
        }

        // ... find the active term ...
        $term = DB::queryOne(
            "SELECT t.id FROM terms t WHERE t.academic_year_id = ? ORDER BY t.is_active DESC, t.term_number DESC LIMIT 1",
            [$yearId]
        );
        // ... rest of autoPromote logic ...

        $students = DB::query(
            "SELECT s.id, s.full_name,
                    COALESCE(SUM(sa.aggregate_score), 0) as aggregate_score,
                    COALESCE(SUM(sa.number_of_subjects), 0) as subject_count
             FROM students s
             LEFT JOIN student_aggregates sa ON sa.student_id = s.id AND sa.class_id = ?
             WHERE (s.current_class_id = ? AND s.academic_year_id = ? AND s.status = 'active')
                OR (s.status = 'active' AND s.id IN (
                    SELECT sa2.student_id FROM student_aggregates sa2
                    JOIN terms t ON t.id = sa2.term_id
                    WHERE sa2.class_id = ? AND t.academic_year_id = ?
                ))
                OR (s.status = 'active' AND s.id IN (
                    SELECT cs.student_id FROM computed_scores cs
                    JOIN class_subjects csb ON csb.id = cs.class_subject_id
                    WHERE csb.class_id = ? AND csb.term_id IN (SELECT id FROM terms WHERE academic_year_id = ?)
                ))
             GROUP BY s.id, s.full_name",
            [$classId, $classId, $yearId, $classId, $yearId, $classId, $yearId]
        );

        try {
            DB::beginTransaction();

            $termId   = $term['id'] ?? null;
            $promoted = 0;
            $repeated = 0;

            // Try to find or auto-create the target class ID in the next year if a name was provided
            $nextClassId = null;
            if ($nextClass) {
                $nextClassId = self::findOrCreateTargetClass($nextClass, $nextYearId, $classId);
            }

            foreach ($students as $student) {
                $sid = $student['id'];

                // Calculate average score if they have subjects
                $avgScore = ($student['subject_count'] > 0)
                    ? ($student['aggregate_score'] / ($student['subject_count'] * 100)) * 100
                    : 0;

                $status = ($avgScore >= $threshold) ? 'promoted' : 'repeated';

                // Upsert promotion record
                DB::execute(
                    "INSERT INTO student_promotions
                        (student_id, academic_year_id, term_id, auto_promoted, promotion_status, next_class_name, set_by)
                     VALUES (?, ?, ?, 1, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        auto_promoted = 1, promotion_status = ?, next_class_name = ?, set_by = ?",
                    [
                        $sid, $yearId, $termId ?? 0, $status, $nextClass, Session::get('user_id'),
                        $status, $nextClass, Session::get('user_id')
                    ]
                );

                if ($status === 'promoted') {
                    // Update student to new year AND target class
                    $sql = "UPDATE students SET academic_year_id = ?";
                    $p   = [$nextYearId];
                    if ($nextClassId) {
                        $sql .= ", current_class_id = ?";
                        $p[] = $nextClassId;
                    }
                    $sql .= " WHERE id = ?";
                    $p[] = $sid;
                    DB::execute($sql, $p);
                    $promoted++;
                } else {
                    $repeated++;
                }
            }

            // ── Teacher Rollover ─────────────────────────────────────
            $rolloverTeachers = !empty($_POST['rollover_teachers']);
            $rolloverMsg = '';

            if ($rolloverTeachers && $nextYearId) {
                // 1. Find the current class details (name + level_id) to match in target year
                $currentClass = DB::queryOne(
                    "SELECT class_name, section, level_id, grading_system FROM classes WHERE id = ?",
                    [$classId]
                );

                if ($currentClass) {
                    // 2. Find or auto-create the equivalent class in the target academic year
                    $targetClass = DB::queryOne(
                        "SELECT id FROM classes WHERE class_name = ? AND level_id = ? AND academic_year_id = ? LIMIT 1",
                        [$currentClass['class_name'], $currentClass['level_id'], $nextYearId]
                    );

                    if (!$targetClass) {
                        $targetClassId = (int)DB::insert(
                            "INSERT INTO classes (level_id, class_name, section, academic_year_id, grading_system) VALUES (?, ?, ?, ?, ?)",
                            [$currentClass['level_id'], $currentClass['class_name'], $currentClass['section'] ?? '', $nextYearId, $currentClass['grading_system'] ?? 'proficiency']
                        );
                    } else {
                        $targetClassId = (int)$targetClass['id'];
                    }

                    if ($targetClassId) {
                        // 3. Copy Form Masters (class_teachers)
                        $formMasters = DB::query(
                            "SELECT teacher_id FROM class_teachers WHERE class_id = ?",
                            [$classId]
                        );
                        foreach ($formMasters as $fm) {
                            DB::execute(
                                "INSERT IGNORE INTO class_teachers (class_id, teacher_id) VALUES (?, ?)",
                                [$targetClassId, $fm['teacher_id']]
                            );
                        }

                        // 4. Find or create Term 1 in the target academic year
                        $targetTerm = DB::queryOne(
                            "SELECT id FROM terms WHERE academic_year_id = ? ORDER BY term_number ASC LIMIT 1",
                            [$nextYearId]
                        );

                        if (!$targetTerm) {
                            // Auto-create Term 1 for the target year
                            $newTermId = DB::insert(
                                "INSERT INTO terms (academic_year_id, name, term_number, is_active) VALUES (?, 'Term 1', 1, 0)",
                                [$nextYearId]
                            );
                            $targetTermId = $newTermId;
                            $rolloverMsg .= ' (Term 1 auto-created)';
                        } else {
                            $targetTermId = $targetTerm['id'];
                        }

                        // 5. Copy subject assignments (class_subjects) from current class & term
                        $currentTermId = $term['id'] ?? null;
                        if ($currentTermId) {
                            $subjectAssignments = DB::query(
                                "SELECT subject_id, teacher_id FROM class_subjects WHERE class_id = ? AND term_id = ?",
                                [$classId, $currentTermId]
                            );

                            $assignedCount = 0;
                            foreach ($subjectAssignments as $sa) {
                                // Check if this class/subject/term combo already exists in target
                                $exists = DB::queryOne(
                                    "SELECT id FROM class_subjects WHERE class_id = ? AND subject_id = ? AND term_id = ? LIMIT 1",
                                    [$targetClassId, $sa['subject_id'], $targetTermId]
                                );

                                if ($exists) {
                                    // Update teacher assignment
                                    DB::execute(
                                        "UPDATE class_subjects SET teacher_id = ? WHERE id = ?",
                                        [$sa['teacher_id'], $exists['id']]
                                    );
                                } else {
                                    DB::insert(
                                        "INSERT INTO class_subjects (class_id, subject_id, teacher_id, term_id) VALUES (?, ?, ?, ?)",
                                        [$targetClassId, $sa['subject_id'], $sa['teacher_id'], $targetTermId]
                                    );
                                }
                                $assignedCount++;
                            }
                            if ($assignedCount > 0) {
                                $rolloverMsg .= " — {$assignedCount} subject teacher(s) automatically assigned to the new session.";
                            }
                        }
                    } else {
                        $rolloverMsg = ' — Teacher assignments skipped: Target class not found.';
                    }
                }
            }

            DB::commit();
            $msg = "Done! {$promoted} student(s) promoted, {$repeated} repeating.";
            if ($rolloverMsg) {
                $msg .= $rolloverMsg;
            }
            Session::flash('success', $msg);
        } catch (\Throwable $e) {
            if (DB::inTransaction()) DB::rollBack();
            Session::flash('error', 'Promotion failed: ' . $e->getMessage());
        }

        $this->redirect($yearId);
    }

    /**
     * Manually set promotion status for a single student (override).
     */
    private function manualPromote(): void {
        $studentId   = (int)($_POST['student_id']    ?? 0);
        $yearId      = (int)($_POST['year_id']        ?? 0);
        $nextYearId  = (int)($_POST['next_year_id']   ?? 0);
        $status      = $_POST['promo_status']         ?? 'promoted';
        $nextClass   = trim($_POST['next_class_name'] ?? '');

        if (!$studentId || !$yearId) {
            Session::flash('error', 'Invalid request.');
            $this->redirect($yearId);
        }

        // Security check for teachers
        if (Session::role() === 'teacher') {
            $check = DB::queryOne("
                SELECT 1 FROM class_teachers ct 
                JOIN students s ON s.current_class_id = ct.class_id 
                WHERE s.id = ? AND ct.teacher_id = ?
            ", [$studentId, Session::userId()]);
            if (!$check) {
                Session::flash('error', 'Access denied: You are not assigned as a Class Teacher for this student.');
                $this->redirect($yearId);
            }
        }

        // Find term
        $term = DB::queryOne(
            "SELECT t.id FROM terms t WHERE t.academic_year_id = ? ORDER BY t.is_active DESC, t.term_number DESC LIMIT 1",
            [$yearId]
        );

        try {
            DB::beginTransaction();

            DB::execute(
                "INSERT INTO student_promotions
                    (student_id, academic_year_id, term_id, auto_promoted, manual_override, promotion_status, next_class_name, set_by)
                 VALUES (?, ?, ?, 0, 1, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    auto_promoted = 0, manual_override = 1, promotion_status = ?, next_class_name = ?, set_by = ?",
                [
                    $studentId, $yearId, $term['id'] ?? 0, $status, $nextClass, Session::get('user_id'),
                    $status, $nextClass, Session::get('user_id')
                ]
            );

            if ($status === 'promoted' && $nextYearId) {
                // Try to find or auto-create target class in next year
                $nextClassId = null;
                if ($nextClass) {
                    $currentStudent = DB::queryOne("SELECT current_class_id FROM students WHERE id = ?", [$studentId]);
                    $srcClassId = (int)($currentStudent['current_class_id'] ?? 0);
                    $nextClassId = self::findOrCreateTargetClass($nextClass, $nextYearId, $srcClassId);
                }

                $sql = "UPDATE students SET academic_year_id = ?";
                $p   = [$nextYearId];
                if ($nextClassId) {
                    $sql .= ", current_class_id = ?";
                    $p[] = $nextClassId;
                }
                $sql .= " WHERE id = ?";
                $p[] = $studentId;
                DB::execute($sql, $p);
            }

            DB::commit();

            $student = DB::queryOne("SELECT full_name FROM students WHERE id = ?", [$studentId]);
            $label   = $status === 'promoted' ? 'passed to next class' : 'set to repeat class';
            Session::flash('success', "Updated result for {$student['full_name']}: {$label}.");
        } catch (\Throwable $e) {
            if (DB::inTransaction()) DB::rollBack();
            Session::flash('error', 'Manual promotion failed: ' . $e->getMessage());
        }

        $this->redirect($yearId);
    }

    /**
     * Revert promotion for an entire class.
     */
    private function unpromoteClass(): void {
        $classId = (int)($_POST['class_id'] ?? 0);
        $yearId  = (int)($_POST['year_id']  ?? 0);

        if (!$classId || !$yearId) {
            Session::flash('error', 'Invalid request.');
            $this->redirect($yearId);
        }

        // Security check for teachers
        if (Session::role() === 'teacher') {
            $assigned = DB::queryOne("SELECT 1 FROM class_teachers WHERE class_id = ? AND teacher_id = ?", [$classId, Session::userId()]);
            if (!$assigned) {
                Session::flash('error', 'Access denied.');
                $this->redirect($yearId);
            }
        }

        // Scope cleanly to students who belong or belonged to this specific class for this academic year
        $students = DB::query("
            SELECT DISTINCT s.id FROM students s
            LEFT JOIN student_promotions sp ON sp.student_id = s.id AND sp.academic_year_id = ?
            WHERE (s.current_class_id = ? AND s.academic_year_id = ?)
               OR (sp.student_id IS NOT NULL AND s.id IN (
                   SELECT sa.student_id FROM student_aggregates sa
                   JOIN terms t ON t.id = sa.term_id
                   WHERE sa.class_id = ? AND t.academic_year_id = ?
               ))
               OR (sp.student_id IS NOT NULL AND s.id IN (
                   SELECT cs.student_id FROM computed_scores cs
                   JOIN class_subjects csb ON csb.id = cs.class_subject_id
                   WHERE csb.class_id = ? AND csb.term_id IN (SELECT id FROM terms WHERE academic_year_id = ?)
               ))
        ", [$yearId, $classId, $yearId, $classId, $yearId, $classId, $yearId]);

        try {
            DB::beginTransaction();

            $count = 0;
            foreach ($students as $s) {
                // Revert year and class to the source year and class
                DB::execute("UPDATE students SET academic_year_id = ?, current_class_id = ? WHERE id = ?", [$yearId, $classId, $s['id']]);
                // Delete promotion record for this source year
                DB::execute("DELETE FROM student_promotions WHERE student_id = ? AND academic_year_id = ?", [$s['id'], $yearId]);
                $count++;
            }

            DB::commit();
            Session::flash('success', "Promotion reset for {$count} student(s). They are now back in their original session.");
        } catch (\Throwable $e) {
            if (DB::inTransaction()) DB::rollBack();
            Session::flash('error', 'Failed to reset promotion: ' . $e->getMessage());
        }

        $this->redirect($yearId);
    }

    /**
     * AJAX: Return JSON list of students for a class with promotion status.
     */
    private function ajaxStudentList(): never {
        $classId = (int)($_GET['class_id'] ?? 0);
        $yearId  = (int)($_GET['year_id']  ?? 0);

        if (!$classId || !$yearId) {
            header('Content-Type: application/json');
            echo json_encode(['students' => []]);
            exit;
        }

        // Security check for teachers
        if (Session::role() === 'teacher') {
            $isCT = DB::queryOne("SELECT 1 FROM class_teachers WHERE class_id = ? AND teacher_id = ?", [$classId, Session::userId()]);
            if (!$isCT) {
                header('Content-Type: application/json');
                echo json_encode(['students' => [], 'error' => 'Access denied']);
                exit;
            }
        }

        $cls = DB::queryOne("SELECT class_name FROM classes WHERE id = ?", [$classId]);
        $cName = $cls['class_name'] ?? '';
        $expectedNext = match($cName) {
            'BASIC 1' => 'BASIC 2',
            'BASIC 2' => 'BASIC 3',
            'BASIC 3' => 'BASIC 4',
            'BASIC 4' => 'BASIC 5',
            'BASIC 5' => 'BASIC 6',
            'BASIC 6' => 'BASIC 7',
            'BASIC 7' => 'BASIC 8',
            'BASIC 8' => 'BASIC 9',
            default   => 'Graduated',
        };

        $students = DB::query(
            "SELECT DISTINCT s.id, s.full_name, s.student_id_number, s.gender,
                    COALESCE(sa.aggregate_score, 0) as aggregate_score,
                    COALESCE(sa.number_of_subjects, 0) as subject_count,
                    sp.promotion_status
             FROM students s
             LEFT JOIN student_aggregates sa ON sa.student_id = s.id AND sa.class_id = ?
             LEFT JOIN student_promotions sp ON sp.student_id = s.id AND sp.academic_year_id = ?
             WHERE (s.current_class_id = ? AND s.academic_year_id = ? AND s.status = 'active')
                OR (s.status = 'active' AND s.id IN (
                    SELECT sa2.student_id FROM student_aggregates sa2
                    JOIN terms t ON t.id = sa2.term_id
                    WHERE sa2.class_id = ? AND t.academic_year_id = ?
                ))
                OR (s.status = 'active' AND s.id IN (
                    SELECT cs.student_id FROM computed_scores cs
                    JOIN class_subjects csb ON csb.id = cs.class_subject_id
                    WHERE csb.class_id = ? AND csb.term_id IN (SELECT id FROM terms WHERE academic_year_id = ?)
                ))
                OR (s.status = 'active' AND sp.academic_year_id = ? AND (sp.next_class_name = ? OR (sp.promotion_status = 'repeated' AND sp.next_class_name = ?)))
             ORDER BY s.gender ASC, s.full_name ASC",
            [$classId, $yearId, $classId, $yearId, $classId, $yearId, $classId, $yearId, $yearId, $expectedNext, $cName]
        );

        // Calculate percentage average per student
        foreach ($students as &$s) {
            $sc = (int)$s['subject_count'];
            $ag = (float)$s['aggregate_score'];
            $s['avg_score'] = ($sc > 0) ? round(($ag / ($sc * 100)) * 100, 1) : 0;
        }

        header('Content-Type: application/json');
        echo json_encode(['students' => $students]);
        exit;
    }

    /**
     * Find existing class in target academic year, or auto-create it if missing.
     */
    public static function findOrCreateTargetClass(string $className, int $targetYearId, int $sourceClassId): int {
        $className = strtoupper(trim($className));
        if (!$className || !$targetYearId) return 0;

        $existing = DB::queryOne(
            "SELECT id FROM classes WHERE class_name = ? AND academic_year_id = ? LIMIT 1",
            [$className, $targetYearId]
        );
        if ($existing) {
            return (int)$existing['id'];
        }

        // Determine level_id and grading_system from source class or infer from name
        $currClass = $sourceClassId ? DB::queryOne("SELECT level_id, section, grading_system FROM classes WHERE id = ?", [$sourceClassId]) : null;
        $levelId   = $currClass['level_id'] ?? 1;
        $grading   = $currClass['grading_system'] ?? 'proficiency';
        $section   = $currClass['section'] ?? '';

        // Infer level from class name if it follows standard Basic school structure (B1-B3 = LP, B4-B6 = UP, B7-B9 = JHS)
        if (preg_match('/(?:BASIC|B)\s*([1-9])/i', $className, $m)) {
            $num = (int)$m[1];
            if ($num <= 3) $levelId = 1;
            elseif ($num <= 6) $levelId = 2;
            else $levelId = 3;
        }

        return (int)DB::insert(
            "INSERT INTO classes (level_id, class_name, section, academic_year_id, grading_system) VALUES (?, ?, ?, ?, ?)",
            [$levelId, $className, $section, $targetYearId, $grading]
        );
    }

    private function redirect(int $yearId = 0): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        $qs   = $yearId ? "?year_id={$yearId}" : '';
        header("Location: {$base}/admin/promotions{$qs}");
        exit;
    }
}
