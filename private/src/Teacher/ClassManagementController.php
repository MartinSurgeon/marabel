<?php
/**
 * Class Management Controller
 * Handles term-level student data: Attendance, Conduct, Attitude, and Remarks.
 * Accessible to Class Teachers.
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';
require_once PRIVATE_PATH . '/src/Helpers/CSRF.php';

class ClassManagementController {

    public function handle(): void {
        Session::requireRole('teacher', 'admin');

        $teacherId = Session::userId();
        $classId   = (int)($_GET['id'] ?? 0);
        
        // 1. Get Active Term
        $term = DB::queryOne(
            "SELECT t.id, t.name, t.total_school_days, ay.year_name 
             FROM terms t
             JOIN academic_years ay ON t.academic_year_id = ay.id
             WHERE t.is_active = 1 LIMIT 1"
        );

        if (!$term) {
            $this->error('No active term found.');
        }

        // 2. Access Check: Is the teacher a class teacher for this class?
        if (Session::role() === 'teacher') {
            $isClassTeacher = DB::queryOne(
                "SELECT class_id FROM class_teachers WHERE class_id = ? AND teacher_id = ?",
                [$classId, $teacherId]
            );
            if (!$isClassTeacher) {
                $this->error('Access denied. You are not assigned as a Class Teacher for this class.');
            }
        }

        $class = DB::queryOne("SELECT id, class_name, section FROM classes WHERE id = ?", [$classId]);
        if (!$class) {
            $this->error('Class not found.');
        }

        // 3. Handle AJAX interactions (Upserts)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->handleAjax($term['id']);
            exit;
        }

        // 4. Fetch Students
        $students = DB::query(
            "SELECT id, student_id_number, full_name, surname 
             FROM students 
             WHERE current_class_id = ? AND status = 'active'
             ORDER BY surname ASC, full_name ASC",
            [$classId]
        );

        // 5. Fetch Existing Attendance
        $attendance = DB::query(
            "SELECT student_id, days_present FROM attendance WHERE term_id = ?",
            [$term['id']]
        );
        $attMap = array_column($attendance, 'days_present', 'student_id');

        // 6. Fetch Existing Remarks
        $remarks = DB::query(
            "SELECT student_id, conduct_character, attitude, teacher_remark, 
                    conduct_remark, interest_remark, attitude_remark
             FROM student_remarks WHERE term_id = ?",
            [$term['id']]
        );
        $remMap = array_column($remarks, null, 'student_id');

        // 7. Fetch Predefined remarks for all categories
        $predefined = DB::query(
            "SELECT content, category FROM predefined_remarks ORDER BY is_system DESC, content ASC"
        );
        $groupedPredefined = [];
        foreach ($predefined as $p) {
            $groupedPredefined[$p['category']][] = $p['content'];
        }

        // Pass globals to template
        global $activeTerm, $targetClass, $studentList, $attData, $remData, $predefinedRemarks;
        $activeTerm  = $term;
        $targetClass = $class;
        $studentList = $students;
        $attData     = $attMap;
        $remData     = $remMap;
        $predefinedRemarks = $groupedPredefined;
    }

    /**
     * Handle AJAX saves for attendance or remarks
     */
    private function handleAjax(int $termId): void {
        header('Content-Type: application/json');
        
        if (!CSRF::verify()) {
            echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
            return;
        }

        $studentId = (int)($_POST['student_id'] ?? 0);
        $field     = $_POST['field'] ?? '';
        $value     = $_POST['value'] ?? '';

        if (!$studentId || !$field) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            return;
        }

        try {
            if ($field === 'days_present') {
                $days = (int)$value;
                $this->upsertAttendance($studentId, $termId, $days);
            } elseif ($field === 'save_predefined') {
                $category = $_POST['category'] ?? 'teacher';
                $this->savePredefinedRemark($value, $category);
            } else {
                $this->upsertRemark($studentId, $termId, $field, $value);
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function upsertAttendance(int $sid, int $tid, int $days): void {
        $exists = DB::queryOne("SELECT id FROM attendance WHERE student_id = ? AND term_id = ?", [$sid, $tid]);
        if ($exists) {
            DB::execute(
                "UPDATE attendance SET days_present = ?, updated_by = ?, updated_at = NOW() WHERE id = ?",
                [$days, Session::userId(), $exists['id']]
            );
        } else {
            DB::execute(
                "INSERT INTO attendance (student_id, term_id, days_present, updated_by) VALUES (?, ?, ?, ?)",
                [$sid, $tid, $days, Session::userId()]
            );
        }
    }

    private function upsertRemark(int $sid, int $tid, string $field, string $val): void {
        // Allowed fields for teacher
        $allowed = [
            'conduct_character', 'attitude', 'teacher_remark', 
            'conduct_remark', 'interest_remark', 'attitude_remark'
        ];
        if (!in_array($field, $allowed)) {
            throw new Exception('Invalid field');
        }

        $exists = DB::queryOne("SELECT id FROM student_remarks WHERE student_id = ? AND term_id = ?", [$sid, $tid]);
        if ($exists) {
            DB::execute(
                "UPDATE student_remarks SET $field = ?, updated_by = ?, updated_at = NOW() WHERE id = ?",
                [$val, Session::userId(), $exists['id']]
            );
        } else {
            DB::execute(
                "INSERT INTO student_remarks (student_id, term_id, $field, updated_by) VALUES (?, ?, ?, ?)",
                [$sid, $tid, $val, Session::userId()]
            );
        }
    }

    private function savePredefinedRemark(string $content, string $category): void {
        $content = trim($content);
        if (!$content) return;

        // Check if already exists
        $exists = DB::queryOne("SELECT id FROM predefined_remarks WHERE content = ? AND category = ?", [$content, $category]);
        if (!$exists) {
            DB::execute(
                "INSERT INTO predefined_remarks (category, content, created_by) VALUES (?, ?, ?)",
                [$category, $content, Session::userId()]
            );
        }
    }

    private function error(string $msg): never {
        Session::flash('error', $msg);
        header('Location: ' . (defined('APP_BASE') ? APP_BASE : '') . '/teacher');
        exit;
    }
}
