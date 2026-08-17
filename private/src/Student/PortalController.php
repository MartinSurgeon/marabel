<?php
/**
 * Student Portal Controller
 * Uaddara Basic School — SBA Management System
 * 
 * Manages the student portal interface (Coming Soon state).
 */

require_once PRIVATE_PATH . '/src/Helpers/Session.php';
require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Config.php';

class PortalController {

    public function handle(): void {
        Session::requireRole('student');

        $studentId = Session::get('student_id') ?? Session::userId();

        global $studentData, $activeTerm, $activeYear;

        $studentData = DB::queryOne(
            "SELECT s.*, c.class_name, c.section, ay.year_name
             FROM students s
             LEFT JOIN classes c ON c.id = s.current_class_id
             LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
             WHERE s.id = ?",
            [$studentId]
        );

        $activeYear = DB::queryOne("SELECT * FROM academic_years WHERE is_active = 1 LIMIT 1");
        $activeTerm = null;
        if ($activeYear) {
            $activeTerm = DB::queryOne("SELECT * FROM terms WHERE is_active = 1 AND academic_year_id = ? LIMIT 1", [$activeYear['id']]);
        }
    }
}
