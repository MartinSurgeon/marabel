<?php
/**
 * Headmaster Remark Controller
 * Allows bulk addition of headmaster remarks to report cards.
 */

class RemarkController {

    public function handle(): void {
        // Session::requireRole('admin');

        $classId = (int)($_GET['class_id'] ?? 0);
        
        // 1. Get Active Term
        $term = DB::queryOne("
            SELECT t.*, ay.year_name 
            FROM terms t
            JOIN academic_years ay ON t.academic_year_id = ay.id
            WHERE t.is_active = 1 LIMIT 1
        ");
        if (!$term) {
            $term = DB::queryOne("
                SELECT t.*, ay.year_name 
                FROM terms t 
                JOIN academic_years ay ON t.academic_year_id = ay.id
                ORDER BY t.id DESC LIMIT 1
            ");
        }

        if (!$term) {
            $this->error('No active academic term found.');
        }

        // Handle AJAX save
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->handleAjax($term['id']);
            exit;
        }

        // 2. Fetch Classes for dropdown
        $classes = DB::query("
            SELECT c.id, c.class_name, c.section, sl.name as level_name
            FROM classes c
            JOIN school_levels sl ON sl.id = c.level_id
            ORDER BY sl.sort_order, c.class_name
        ");

        // 3. If class selected, fetch students and remarks
        $students = [];
        if ($classId) {
            $students = DB::query("
                SELECT s.id, s.full_name, s.student_id_number, 
                       r.teacher_remark, r.headmaster_remark, r.conduct_character, r.attitude
                FROM students s
                LEFT JOIN student_remarks r ON r.student_id = s.id AND r.term_id = ?
                WHERE s.current_class_id = ? AND s.status = 'active'
                ORDER BY s.full_name ASC
            ", [$term['id'], $classId]);
        }

        // 4. Fetch Predefined remarks for all categories
        $predefined = DB::query(
            "SELECT content, category FROM predefined_remarks ORDER BY is_system DESC, content ASC"
        );
        $groupedPredefined = [];
        foreach ($predefined as $p) {
            $groupedPredefined[$p['category']][] = $p['content'];
        }

        global $activeTerm, $classList, $studentList, $predefinedRemarks, $selectedClassId;
        $activeTerm = $term;
        $classList  = $classes;
        $studentList = $students;
        $selectedClassId = $classId;
        $predefinedRemarks = $groupedPredefined;
    }

    private function handleAjax(int $termId): void {
        header('Content-Type: application/json');
        
        if (!CSRF::verify()) {
            echo json_encode(['success' => false, 'message' => 'CSRF failed']);
            return;
        }

        $sid   = (int)($_POST['student_id'] ?? 0);
        $field = $_POST['field'] ?? '';
        $val   = $_POST['value'] ?? '';

        if (!$sid || !$field) {
            echo json_encode(['success' => false, 'message' => 'Missing params']);
            return;
        }

        $allowed = ['headmaster_remark', 'teacher_remark'];
        if (!in_array($field, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid field']);
            return;
        }

        try {
            $exists = DB::queryOne("SELECT id FROM student_remarks WHERE student_id = ? AND term_id = ?", [$sid, $termId]);
            if ($exists) {
                DB::execute(
                    "UPDATE student_remarks SET $field = ?, updated_by = ?, updated_at = NOW() WHERE id = ?",
                    [$val, Session::userId(), $exists['id']]
                );
            } else {
                DB::execute(
                    "INSERT INTO student_remarks (student_id, term_id, $field, updated_by) VALUES (?, ?, ?, ?)",
                    [$sid, $termId, $val, Session::userId()]
                );
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function savePredefined(string $content, string $category = 'headmaster'): void {
        $content = trim($content);
        if (!$content) return;
        $exists = DB::queryOne("SELECT id FROM predefined_remarks WHERE category=? AND content=?", [$category, $content]);
        if (!$exists) {
            DB::execute("INSERT INTO predefined_remarks (category, content, created_by) VALUES (?, ?, ?)", [$category, $content, Session::userId()]);
        }
    }

    private function error(string $msg): never {
        Session::flash('error', $msg);
        header('Location: ' . APP_BASE . '/admin');
        exit;
    }
}
