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
            $classTeacherFor = [];
        } else {
            // 2. Get Assigned classes/subjects for this teacher in this term
            $assigned = DB::query(
                "SELECT 
                    cs.id as class_subject_id,
                    c.class_name,
                    c.section,
                    s.subject_name,
                    sl.name as level_name
                 FROM class_subjects cs
                 JOIN classes c ON cs.class_id = c.id
                 JOIN subjects s ON cs.subject_id = s.id
                 JOIN school_levels sl ON c.level_id = sl.id
                 WHERE cs.teacher_id = ? AND cs.term_id = ?
                 ORDER BY c.class_name ASC, s.sort_order ASC",
                [$teacherId, $term['id']]
            );

            // 3. Get Classes where this teacher is the "Class Teacher"
            $classTeacherFor = DB::query(
                "SELECT c.id, c.class_name, c.section 
                 FROM classes c
                 JOIN class_teachers ct ON ct.class_id = c.id
                 WHERE ct.teacher_id = ?",
                [$teacherId]
            );

            // 4. Calculate Stats for Summary Cards
            $managedClassIds = array_column($classTeacherFor, 'id');
            $subjectClassIds = array_column($assigned, 'class_id'); // Not in current select, need to add it or use union
            
            $allClassIds = array_unique(array_merge($managedClassIds, $subjectClassIds));
            
            $queryBase = "FROM students st WHERE st.current_class_id IN (
                            SELECT class_id FROM class_subjects WHERE teacher_id = ? AND term_id = ?
                            UNION
                            SELECT class_id FROM class_teachers WHERE teacher_id = ?
                         ) AND st.status = 'active'";

            $stats = [
                'students'        => (int)DB::queryValue("SELECT COUNT(DISTINCT st.id) $queryBase", [$teacherId, $term['id'], $teacherId]),
                'students_male'   => (int)DB::queryValue("SELECT COUNT(DISTINCT st.id) $queryBase AND st.gender = 'Male'", [$teacherId, $term['id'], $teacherId]),
                'students_female' => (int)DB::queryValue("SELECT COUNT(DISTINCT st.id) $queryBase AND st.gender = 'Female'", [$teacherId, $term['id'], $teacherId]),
                
                'subjects' => (int)DB::queryValue(
                    "SELECT COUNT(DISTINCT subject_id) FROM class_subjects WHERE teacher_id = ? AND term_id = ?",
                    [$teacherId, $term['id']]
                ),
                'classes' => (int)DB::queryValue(
                    "SELECT COUNT(DISTINCT class_id) FROM class_subjects WHERE teacher_id = ? AND term_id = ?",
                    [$teacherId, $term['id']]
                ),
                'managed' => count($classTeacherFor)
            ];
        }

        // 5. Summarize Roles
        $roles = [];
        if (!empty($classTeacherFor)) $roles[] = 'Class Teacher';
        if (!empty($assigned))        $roles[] = 'Subject Teacher';
        
        if (empty($roles)) $roles[] = 'Teacher';

        // Global variables for template
        global $activeTerm, $myClasses, $mySubjects, $userRoles, $dashboardStats;
        $activeTerm = $term;
        $myClasses  = $classTeacherFor;
        $mySubjects = $assigned;
        $userRoles  = $roles;
        $dashboardStats = $stats ?? ['students' => 0, 'subjects' => 0, 'classes' => 0, 'managed' => 0];

    }
}
