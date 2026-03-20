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
                default             => $this->redirect(),
            };
        }

        // ── Prepare view data ─────────────────────────────────────────
        global $pageYear, $yearsList, $classesSummary, $nextYearsList;

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

        // Summarise each class: total students, auto-promotable, needs manual review
        if ($filterYearId) {
            $classesSummary = DB::query(
                "SELECT
                    c.id, c.class_name, c.section, sl.name as level_name, sl.code as level_code,
                    COUNT(s.id) as total_students,
                    SUM(CASE WHEN sp.promotion_status = 'promoted' THEN 1 ELSE 0 END) as promoted_count,
                    SUM(CASE WHEN sp.promotion_status = 'repeated' THEN 1 ELSE 0 END) as repeated_count,
                    SUM(CASE WHEN sp.promotion_status = 'pending' OR sp.promotion_status IS NULL THEN 1 ELSE 0 END) as pending_count
                 FROM classes c
                 JOIN school_levels sl ON sl.id = c.level_id
                 LEFT JOIN students s ON s.current_class_id = c.id AND s.status = 'active' AND s.academic_year_id = c.academic_year_id
                 LEFT JOIN terms t ON t.academic_year_id = c.academic_year_id AND t.is_active = 1
                 LEFT JOIN student_promotions sp ON sp.student_id = s.id AND sp.academic_year_id = c.academic_year_id
                 WHERE c.academic_year_id = ?
                 GROUP BY c.id, c.class_name, c.section, sl.name, sl.code
                 ORDER BY sl.sort_order, c.class_name",
                [$filterYearId]
            );
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

        // Find the active term for this year
        $term = DB::queryOne(
            "SELECT t.id FROM terms t WHERE t.academic_year_id = ? ORDER BY t.is_active DESC, t.term_number DESC LIMIT 1",
            [$yearId]
        );

        $students = DB::query(
            "SELECT s.id, s.full_name,
                    COALESCE(sa.aggregate_score, 0) as aggregate_score,
                    COALESCE(sa.number_of_subjects, 0) as subject_count
             FROM students s
             LEFT JOIN student_aggregates sa ON sa.student_id = s.id AND sa.class_id = ?
             WHERE s.current_class_id = ? AND s.status = 'active' AND s.academic_year_id = ?",
            [$classId, $classId, $yearId]
        );

        $termId   = $term['id'] ?? null;
        $promoted = 0;
        $repeated = 0;

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
                // Update student to new year (class_id will be set when admin assigns for next year)
                DB::execute(
                    "UPDATE students SET academic_year_id = ? WHERE id = ?",
                    [$nextYearId, $sid]
                );
                $promoted++;
            } else {
                $repeated++;
            }
        }

        Session::flash('success', "Auto-promotion complete: {$promoted} promoted, {$repeated} held back.");
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

        // Find term
        $term = DB::queryOne(
            "SELECT t.id FROM terms t WHERE t.academic_year_id = ? ORDER BY t.is_active DESC, t.term_number DESC LIMIT 1",
            [$yearId]
        );

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
            DB::execute("UPDATE students SET academic_year_id = ? WHERE id = ?", [$nextYearId, $studentId]);
        }

        $student = DB::queryOne("SELECT full_name FROM students WHERE id = ?", [$studentId]);
        $label   = $status === 'promoted' ? 'promoted' : 'held back';
        Session::flash('success', "{$student['full_name']} has been manually {$label}.");
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

        $students = DB::query(
            "SELECT s.id, s.full_name, s.student_id_number,
                    COALESCE(sa.aggregate_score, 0) as aggregate_score,
                    COALESCE(sa.number_of_subjects, 0) as subject_count,
                    sp.promotion_status
             FROM students s
             LEFT JOIN student_aggregates sa ON sa.student_id = s.id AND sa.class_id = ?
             LEFT JOIN student_promotions sp ON sp.student_id = s.id AND sp.academic_year_id = ?
             WHERE s.current_class_id = ? AND s.status = 'active' AND s.academic_year_id = ?
             ORDER BY s.full_name ASC",
            [$classId, $yearId, $classId, $yearId]
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

    private function redirect(int $yearId = 0): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        $qs   = $yearId ? "?year_id={$yearId}" : '';
        header("Location: {$base}/admin/promotions{$qs}");
        exit;
    }
}
