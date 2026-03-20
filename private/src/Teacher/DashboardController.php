<?php
/**
 * Teacher Dashboard Controller
 * Uaddara Basic School — SBA Management System
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';

class DashboardController {

    public function handle(): void {
        Session::requireRole('teacher', 'admin');
        
        $teacherId = Session::userId();
        
        // 1. Get Active Term
        $term = DB::queryOne(
            "SELECT t.*, ay.year_name FROM terms t
             JOIN academic_years ay ON t.academic_year_id = ay.id
             WHERE t.is_active = 1 LIMIT 1"
        );

        if (!$term) {
            // Handle no active term
            $assigned = [];
        } else {
            // 2. Get Assigned classes/subjects for this teacher in this term
            // We join with students count to show progress
            $assigned = DB::query(
                "SELECT 
                    cs.id as class_subject_id,
                    cs.is_locked,
                    c.class_name,
                    c.section,
                    s.subject_name,
                    s.subject_code,
                    sl.name as level_name,
                    (SELECT COUNT(*) FROM students st WHERE st.current_class_id = c.id AND st.status = 'active') as student_count,
                    (SELECT COUNT(*) FROM sba_component_scores scs 
                     WHERE scs.class_subject_id = cs.id AND scs.term_id = ? AND scs.sub_total IS NOT NULL) as sba_completed_count,
                    (SELECT COUNT(*) FROM exam_scores es 
                     WHERE es.class_subject_id = cs.id AND es.term_id = ? AND es.raw_score IS NOT NULL) as exam_completed_count
                 FROM class_subjects cs
                 JOIN classes c ON cs.class_id = c.id
                 JOIN subjects s ON cs.subject_id = s.id
                 JOIN school_levels sl ON s.level_id = sl.id
                 WHERE cs.teacher_id = ? AND cs.term_id = ?
                 ORDER BY c.class_name ASC, s.sort_order ASC",
                [$term['id'], $term['id'], $teacherId, $term['id']]
            );
        }

        // Global variables for template
        global $activeTerm, $assignedBundles;
        $activeTerm = $term;
        $assignedBundles = $assigned;
    }
}
