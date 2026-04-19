<?php
/**
 * Report Selection Controller
 * Lists students assigned to the teacher for report card preview.
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';

class ReportSelectionController {

    public function handle(): void {
        Session::requireRole('teacher', 'admin');
        
        $teacherId = Session::userId();
        
        // 1. Get Active Term
        $term = DB::queryOne(
            "SELECT t.id, t.name, ay.year_name FROM terms t
             JOIN academic_years ay ON t.academic_year_id = ay.id
             WHERE t.is_active = 1 LIMIT 1"
        );

        if (!$term) {
            $this->render([], null);
            return;
        }

        // 2. Get all students that this teacher is either a Class Teacher or Subject Teacher for
        $students = DB::query(
            "SELECT DISTINCT 
                s.id, s.student_id_number, s.full_name, s.surname, s.gender,
                c.class_name, c.section, c.id as class_id
             FROM students s
             JOIN classes c ON s.current_class_id = c.id
             LEFT JOIN class_subjects cs ON cs.class_id = c.id
             LEFT JOIN class_teachers ct ON ct.class_id = c.id
             WHERE (cs.teacher_id = ? AND cs.term_id = ?) 
                OR ct.teacher_id = ?
                OR 'admin' = ?
             ORDER BY c.class_name ASC, s.gender ASC, s.full_name ASC",
            [$teacherId, $term['id'], $teacherId, Session::role()]
        );

        // 3. Group students by class
        $grouped = [];
        foreach ($students as $s) {
            $classKey = $s['class_name'] . ($s['section'] ? " ({$s['section']})" : '');
            $grouped[$classKey][] = $s;
        }

        $this->render($grouped, $term);
    }

    private function render(array $groupedStudents, ?array $term): void {
        global $reportGroups, $activeTerm;
        $reportGroups = $groupedStudents;
        $activeTerm   = $term;
    }
}
