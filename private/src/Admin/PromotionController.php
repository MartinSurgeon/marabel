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

                $counts = DB::queryOne("
                    SELECT 
                        COUNT(DISTINCT s.id) as total_students,
                        COUNT(DISTINCT CASE WHEN sp.promotion_status = 'promoted' THEN s.id END) as promoted_count,
                        COUNT(DISTINCT CASE WHEN sp.promotion_status = 'repeated' THEN s.id END) as repeated_count
                    FROM students s
                    LEFT JOIN student_promotions sp ON sp.student_id = s.id AND sp.academic_year_id = ? AND sp.from_class_id = ?
                    WHERE (s.current_class_id = ? AND s.academic_year_id = ?)
                       OR (sp.from_class_id = ? AND sp.academic_year_id = ?)
                ", [$filterYearId, $cid, $cid, $filterYearId, $cid, $filterYearId]);

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
        $classId       = (int)($_POST['class_id']        ?? 0);
        $yearId        = (int)($_POST['year_id']         ?? 0);
        $nextYearId    = (int)($_POST['next_year_id']     ?? 0);
        $targetClassIn = trim($_POST['target_class_id']  ?? $_POST['next_class_name'] ?? '');
        $threshold     = (float)($_POST['threshold']      ?? 50.0);

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

        $srcClass = DB::queryOne("SELECT class_name, section, level_id FROM classes WHERE id = ?", [$classId]);
        if (!$srcClass) {
            Session::flash('error', 'Source class not found.');
            $this->redirect($yearId);
        }
        $srcClassName = $srcClass['class_name'];
        $srcSection   = $srcClass['section'] ?? '';
        $isTerminal   = in_array(strtoupper($srcClassName), ['BASIC 9', 'JHS 3']);

        // Find active term for source year
        $term = DB::queryOne(
            "SELECT t.id FROM terms t WHERE t.academic_year_id = ? ORDER BY t.is_active DESC, t.term_number DESC LIMIT 1",
            [$yearId]
        );
        $termId = $term['id'] ?? null;

        $isSplitAB     = ($targetClassIn === 'split_ab' || (strtoupper($srcClassName) === 'BASIC 4' && ($targetClassIn === '' || $targetClassIn === 'split_ab')));
        $isMeritStream = ($targetClassIn === 'stream_merit' || (in_array(strtoupper($srcClassName), ['BASIC 8', 'JHS 2']) && $targetClassIn === 'stream_merit'));

        // Resolve Target Class for promoted students
        $nextClassId   = null;
        $nextClassName = '';
        $target5AId    = null;
        $target5BId    = null;
        $target9AId    = null;
        $target9BId    = null;

        if ($isTerminal) {
            $nextClassName = 'Graduated';
        } elseif ($isSplitAB) {
            $target5AId = self::findOrCreateTargetClass('BASIC 5', 'A', $nextYearId, $classId);
            $target5BId = self::findOrCreateTargetClass('BASIC 5', 'B', $nextYearId, $classId);
            $nextClassName = 'BASIC 5 (A/B Split)';
        } elseif ($isMeritStream) {
            $target9AId = self::findOrCreateTargetClass('BASIC 9', 'A', $nextYearId, $classId);
            $target9BId = self::findOrCreateTargetClass('BASIC 9', 'B', $nextYearId, $classId);
            $nextClassName = 'BASIC 9 (Merit Stream)';
        } else {
            if (is_numeric($targetClassIn) && (int)$targetClassIn > 0) {
                $targetRow = DB::queryOne("SELECT id, class_name, section FROM classes WHERE id = ? AND academic_year_id = ?", [(int)$targetClassIn, $nextYearId]);
                if ($targetRow) {
                    $nextClassId   = (int)$targetRow['id'];
                    $nextClassName = $targetRow['class_name'] . ($targetRow['section'] ? ' ' . $targetRow['section'] : '');
                }
            } elseif (str_starts_with($targetClassIn, 'auto:')) {
                $classNameReq  = substr($targetClassIn, 5);
                $nextClassId   = self::findOrCreateTargetClass($classNameReq, $srcSection, $nextYearId, $classId);
                $nextClassName = $classNameReq;
            } elseif ($targetClassIn !== '') {
                $nextClassId   = self::findOrCreateTargetClass($targetClassIn, $srcSection, $nextYearId, $classId);
                $nextClassName = $targetClassIn;
            }
        }

        // Equivalent class for repeating students in target year
        $repeatClassId = self::findOrCreateTargetClass($srcClassName, $srcSection, $nextYearId, $classId);

        // Fetch students belonging to this class in this year
        $students = DB::query(
            "SELECT s.id, s.full_name, s.student_id_number,
                    COALESCE(SUM(sa.aggregate_score), 0) as aggregate_score,
                    COALESCE(SUM(sa.number_of_subjects), 0) as subject_count,
                    sp.manual_override,
                    sp.promotion_status as existing_status
             FROM students s
             LEFT JOIN student_aggregates sa ON sa.student_id = s.id AND sa.class_id = ?
             LEFT JOIN student_promotions sp ON sp.student_id = s.id AND sp.academic_year_id = ? AND sp.from_class_id = ?
             WHERE (s.current_class_id = ? AND s.academic_year_id = ? AND s.status = 'active')
                OR (sp.from_class_id = ? AND sp.academic_year_id = ?)
             GROUP BY s.id, s.full_name, s.student_id_number",
            [$classId, $yearId, $classId, $classId, $yearId, $classId, $yearId]
        );

        // Calculate avg_score and initial status for all students
        foreach ($students as &$st) {
            $sc = (int)$st['subject_count'];
            $ag = (float)$st['aggregate_score'];
            $st['avg_score'] = ($sc > 0) ? ($ag / ($sc * 100)) * 100 : 0;

            if (!empty($st['manual_override']) && !empty($st['existing_status'])) {
                $st['promo_status'] = $st['existing_status'];
            } else {
                $st['promo_status'] = ($st['avg_score'] >= $threshold) ? 'promoted' : 'repeated';
            }
        }
        unset($st);

        // If Merit Streaming (Basic 8 -> Basic 9): Rank promoted students so top performers get 9B, rest get 9A
        $meritAssignments = [];
        if ($isMeritStream) {
            $promotedStudents = array_filter($students, fn($s) => $s['promo_status'] === 'promoted');
            // Sort by avg_score DESC (highest first)
            usort($promotedStudents, fn($a, $b) => $b['avg_score'] <=> $a['avg_score']);
            $promCount = count($promotedStudents);
            $topHalfCut = (int)ceil($promCount / 2);

            $rank = 0;
            foreach ($promotedStudents as $ps) {
                // Top half -> 9B (high grades); Lower half -> 9A
                if ($rank < $topHalfCut) {
                    $meritAssignments[$ps['id']] = ['id' => $target9BId, 'name' => 'BASIC 9 B'];
                } else {
                    $meritAssignments[$ps['id']] = ['id' => $target9AId, 'name' => 'BASIC 9 A'];
                }
                $rank++;
            }
        }

        try {
            DB::beginTransaction();

            $promoted  = 0;
            $repeated  = 0;
            $graduated = 0;
            $splitIdx  = 0;

            foreach ($students as $student) {
                $sid    = (int)$student['id'];
                $status = $student['promo_status'];

                if ($status === 'promoted') {
                    if ($isTerminal) {
                        $assignedTargetId = null;
                        $assignedNextName = 'Graduated';
                    } elseif ($isSplitAB) {
                        // Break even 50/50 split across Section A and Section B
                        if ($splitIdx % 2 === 0) {
                            $assignedTargetId = $target5AId;
                            $assignedNextName = 'BASIC 5 A';
                        } else {
                            $assignedTargetId = $target5BId;
                            $assignedNextName = 'BASIC 5 B';
                        }
                        $splitIdx++;
                    } elseif ($isMeritStream) {
                        $m = $meritAssignments[$sid] ?? ['id' => $target9BId, 'name' => 'BASIC 9 B'];
                        $assignedTargetId = $m['id'];
                        $assignedNextName = $m['name'];
                    } else {
                        $assignedTargetId = $nextClassId;
                        $assignedNextName = $nextClassName;
                    }
                } else {
                    $assignedTargetId = $repeatClassId;
                    $assignedNextName = $srcClassName;
                }

                // Upsert promotion record
                DB::execute(
                    "INSERT INTO student_promotions
                        (student_id, academic_year_id, term_id, from_class_id, auto_promoted, manual_override, promotion_status, target_class_id, next_class_name, set_by)
                     VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        from_class_id    = VALUES(from_class_id),
                        auto_promoted    = 1,
                        promotion_status = VALUES(promotion_status),
                        target_class_id  = VALUES(target_class_id),
                        next_class_name  = VALUES(next_class_name),
                        set_by           = VALUES(set_by)",
                    [
                        $sid, $yearId, $termId ?? 0, $classId,
                        !empty($student['manual_override']) ? 1 : 0,
                        $status, $assignedTargetId, $assignedNextName,
                        Session::get('user_id')
                    ]
                );

                if ($status === 'promoted') {
                    if ($isTerminal) {
                        // Terminal students graduate and are archived
                        DB::execute("UPDATE students SET status = 'inactive' WHERE id = ?", [$sid]);
                        $graduated++;
                    } else {
                        // Move to next year and assigned target class
                        $sql = "UPDATE students SET academic_year_id = ?, status = 'active'";
                        $p   = [$nextYearId];
                        if ($assignedTargetId) {
                            $sql .= ", current_class_id = ?";
                            $p[]  = $assignedTargetId;
                        }
                        $sql .= " WHERE id = ?";
                        $p[]  = $sid;
                        DB::execute($sql, $p);
                        $promoted++;
                    }
                } else {
                    // Repeating student moves to new year in their repeating class
                    $sql = "UPDATE students SET academic_year_id = ?, status = 'active'";
                    $p   = [$nextYearId];
                    if ($repeatClassId) {
                        $sql .= ", current_class_id = ?";
                        $p[]  = $repeatClassId;
                    }
                    $sql .= " WHERE id = ?";
                    $p[]  = $sid;
                    DB::execute($sql, $p);
                    $repeated++;
                }
            }

            DB::commit();

            if ($isTerminal) {
                $msg = "Done! {$graduated} student(s) graduated, {$repeated} repeating.";
            } else {
                $msg = "Done! {$promoted} student(s) promoted, {$repeated} repeating.";
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
        $studentId     = (int)($_POST['student_id']       ?? 0);
        $yearId        = (int)($_POST['year_id']          ?? 0);
        $nextYearId    = (int)($_POST['next_year_id']     ?? 0);
        $status        = $_POST['promo_status']           ?? 'promoted';
        $targetClassIn = trim($_POST['target_class_id']   ?? $_POST['next_class_name'] ?? '');

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

        $currentStudent = DB::queryOne("SELECT current_class_id, academic_year_id FROM students WHERE id = ?", [$studentId]);
        $srcClassId     = 0;
        if ($currentStudent && (int)$currentStudent['academic_year_id'] === $yearId) {
            $srcClassId = (int)$currentStudent['current_class_id'];
        } else {
            $prev = DB::queryOne("SELECT from_class_id FROM student_promotions WHERE student_id = ? AND academic_year_id = ?", [$studentId, $yearId]);
            $srcClassId = (int)($prev['from_class_id'] ?? $currentStudent['current_class_id'] ?? 0);
        }
        $srcClassRow  = $srcClassId ? DB::queryOne("SELECT class_name, section FROM classes WHERE id = ?", [$srcClassId]) : null;
        $srcClassName = $srcClassRow['class_name'] ?? '';
        $srcSection   = $srcClassRow['section'] ?? '';
        $isTerminal   = in_array(strtoupper($srcClassName), ['BASIC 9', 'JHS 3']);

        try {
            DB::beginTransaction();

            $targetClassId   = null;
            $targetClassName = '';

            if ($status === 'promoted') {
                if ($isTerminal || strtoupper($targetClassIn) === 'GRADUATED') {
                    $targetClassName = 'Graduated';
                    $targetClassId   = null;
                } else {
                    if (is_numeric($targetClassIn) && (int)$targetClassIn > 0 && $nextYearId) {
                        $targetRow = DB::queryOne("SELECT id, class_name, section FROM classes WHERE id = ? AND academic_year_id = ?", [(int)$targetClassIn, $nextYearId]);
                        if ($targetRow) {
                            $targetClassId   = (int)$targetRow['id'];
                            $targetClassName = $targetRow['class_name'] . ($targetRow['section'] ? ' ' . $targetRow['section'] : '');
                        }
                    } elseif ($targetClassIn !== '' && $nextYearId) {
                        $targetClassId   = self::findOrCreateTargetClass($targetClassIn, $srcSection, $nextYearId, $srcClassId);
                        $targetClassName = $targetClassIn;
                    }
                }
            } else {
                // Repeating
                $targetClassName = $srcClassName;
                if ($nextYearId) {
                    $targetClassId = self::findOrCreateTargetClass($srcClassName, $srcSection, $nextYearId, $srcClassId);
                }
            }

            DB::execute(
                "INSERT INTO student_promotions
                    (student_id, academic_year_id, term_id, from_class_id, auto_promoted, manual_override, promotion_status, target_class_id, next_class_name, set_by)
                 VALUES (?, ?, ?, ?, 0, 1, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    from_class_id    = VALUES(from_class_id),
                    auto_promoted    = 0,
                    manual_override  = 1,
                    promotion_status = VALUES(promotion_status),
                    target_class_id  = VALUES(target_class_id),
                    next_class_name  = VALUES(next_class_name),
                    set_by           = VALUES(set_by)",
                [
                    $studentId, $yearId, $term['id'] ?? 0, $srcClassId,
                    $status, $targetClassId, $targetClassName, Session::get('user_id')
                ]
            );

            if ($nextYearId) {
                if ($status === 'promoted' && $isTerminal) {
                    DB::execute("UPDATE students SET status = 'inactive' WHERE id = ?", [$studentId]);
                } else {
                    $sql = "UPDATE students SET academic_year_id = ?, status = 'active'";
                    $p   = [$nextYearId];
                    if ($targetClassId) {
                        $sql .= ", current_class_id = ?";
                        $p[]  = $targetClassId;
                    }
                    $sql .= " WHERE id = ?";
                    $p[]  = $studentId;
                    DB::execute($sql, $p);
                }
            }

            DB::commit();

            $student = DB::queryOne("SELECT full_name FROM students WHERE id = ?", [$studentId]);
            $label   = ($status === 'promoted') ? ($isTerminal ? 'marked as Graduated' : 'passed to next class') : 'set to repeat class';
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

        $students = DB::query("
            SELECT DISTINCT sp.student_id
            FROM student_promotions sp
            WHERE sp.from_class_id = ? AND sp.academic_year_id = ?
        ", [$classId, $yearId]);

        try {
            DB::beginTransaction();

            $count = 0;
            foreach ($students as $s) {
                $sid = (int)$s['student_id'];
                // Revert year and class to the source year and class, and reactivate
                DB::execute("UPDATE students SET academic_year_id = ?, current_class_id = ?, status = 'active' WHERE id = ?", [$yearId, $classId, $sid]);
                // Delete promotion record for this source year
                DB::execute("DELETE FROM student_promotions WHERE student_id = ? AND academic_year_id = ? AND from_class_id = ?", [$sid, $yearId, $classId]);
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

        $cls = DB::queryOne("SELECT class_name, section FROM classes WHERE id = ?", [$classId]);
        $cName = $cls['class_name'] ?? '';
        $isTerminal = in_array(strtoupper($cName), ['BASIC 9', 'JHS 3']);
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
                    sp.promotion_status, sp.manual_override, sp.target_class_id, sp.next_class_name
             FROM students s
             LEFT JOIN student_aggregates sa ON sa.student_id = s.id AND sa.class_id = ?
             LEFT JOIN student_promotions sp ON sp.student_id = s.id AND sp.academic_year_id = ? AND sp.from_class_id = ?
             WHERE (s.current_class_id = ? AND s.academic_year_id = ?)
                OR (sp.from_class_id = ? AND sp.academic_year_id = ?)
             ORDER BY s.gender ASC, s.full_name ASC",
            [$classId, $yearId, $classId, $classId, $yearId, $classId, $yearId]
        );

        // Calculate percentage average per student
        foreach ($students as &$s) {
            $sc = (int)$s['subject_count'];
            $ag = (float)$s['aggregate_score'];
            $s['avg_score'] = ($sc > 0) ? round(($ag / ($sc * 100)) * 100, 1) : 0;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'students'       => $students,
            'is_terminal'    => $isTerminal,
            'expected_next'  => $expectedNext,
            'source_section' => $cls['section'] ?? '',
        ]);
        exit;
    }

    /**
     * Find existing class in target academic year, or auto-create it if missing.
     */
    public static function findOrCreateTargetClass(string $className, string $section = '', int $targetYearId = 0, int $sourceClassId = 0): int {
        $className = strtoupper(trim($className));
        if ($className === 'GRADUATED' || empty($className) || $targetYearId <= 0) {
            return 0;
        }

        $currClass = $sourceClassId ? DB::queryOne("SELECT level_id, section, grading_system FROM classes WHERE id = ?", [$sourceClassId]) : null;
        $levelId   = $currClass['level_id'] ?? 1;
        $grading   = $currClass['grading_system'] ?? 'proficiency';
        if ($section === '' && $currClass) {
            $section = $currClass['section'] ?? '';
        }

        // 1. Try exact match on name AND section
        $existing = DB::queryOne(
            "SELECT id FROM classes WHERE class_name = ? AND (section = ? OR (section IS NULL AND ? = '')) AND academic_year_id = ? LIMIT 1",
            [$className, $section, $section, $targetYearId]
        );
        if ($existing) {
            return (int)$existing['id'];
        }

        // 2. If section is empty, check if target year has sectioned classes (e.g. Section A exists)
        if ($section === '') {
            $sectionA = DB::queryOne(
                "SELECT id FROM classes WHERE class_name = ? AND section = 'A' AND academic_year_id = ? LIMIT 1",
                [$className, $targetYearId]
            );
            if ($sectionA) {
                return (int)$sectionA['id'];
            }
        }

        // 3. Infer level from class name if it follows standard Basic school structure (B1-B3 = LP, B4-B6 = UP, B7-B9 = JHS)
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
