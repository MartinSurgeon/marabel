<?php
/**
 * Admin Dashboard Controller
 * Uaddara Basic School — SBA Management System
 */

class DashboardController {

    public function handle(): void {
        global $stats, $activeYear, $activeTerm, $gradingProgress, $pendingPublish, $teacherCommitments;
        
        // 1. Basic Stats
        $stats = [
            'students' => DB::queryOne("SELECT COUNT(*) as c FROM students WHERE status = 'active'")['c'] ?? 0,
            'teachers' => DB::queryOne("SELECT COUNT(*) as c FROM users WHERE role = 'teacher' AND is_active = 1")['c'] ?? 0,
            'classes'  => DB::queryOne("SELECT COUNT(*) as c FROM classes WHERE academic_year_id = (SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1)")['c'] ?? 0,
        ];

        // 2. Active Term Analytics
        $activeYear = DB::queryOne("SELECT id, year_name FROM academic_years WHERE is_active = 1 LIMIT 1")
                   ?? DB::queryOne("SELECT id, year_name FROM academic_years ORDER BY year_name DESC LIMIT 1");

        $activeTerm = DB::queryOne("SELECT * FROM terms WHERE is_active = 1 LIMIT 1");
        if (!$activeTerm && $activeYear) {
            $activeTerm = DB::queryOne(
                "SELECT * FROM terms WHERE academic_year_id = ? ORDER BY term_number DESC LIMIT 1",
                [$activeYear['id']]
            );
        }

        // Initialize empty arrays if no valid term is found
        if (!$activeTerm || !$activeYear) {
            $gradingProgress = ['expected_scores' => 0, 'entered_sba' => 0, 'entered_exam' => 0];
            $pendingPublish = [];
            $teacherCommitments = [];
            return;
        }

        // 3. Grading Progress (School-wide for the active term)
        // expected_scores = (Total Students in each active Class) * (Number of Subjects assigned to that class)
        $progressStats = DB::queryOne("
            SELECT 
                (SELECT SUM(student_count * subject_count) FROM (
                    SELECT c.id, 
                           (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'active') as student_count,
                           (SELECT COUNT(*) FROM class_subjects cs WHERE cs.class_id = c.id AND cs.term_id = ?) as subject_count
                    FROM classes c 
                    WHERE c.academic_year_id = ?
                ) as expected) as expected_scores,
                (SELECT COUNT(*) FROM sba_component_scores WHERE term_id = ?) as entered_sba,
                (SELECT COUNT(*) FROM exam_scores WHERE term_id = ?) as entered_exam
        ", [$activeTerm['id'], $activeYear['id'], $activeTerm['id'], $activeTerm['id']]);

        $gradingProgress = [
            'expected_scores' => (int)($progressStats['expected_scores'] ?? 0),
            'entered_sba'    => (int)($progressStats['entered_sba'] ?? 0),
            'entered_exam'   => (int)($progressStats['entered_exam'] ?? 0)
        ];

        // 4. Pending Publish (Classes that have locked subjects but haven't published yet)
        $pendingPublish = DB::query("
            SELECT c.id, c.class_name, c.section, sl.name AS level_name,
                   (SELECT COUNT(*) FROM class_subjects cs WHERE cs.class_id = c.id AND cs.term_id = ?) as total_subjects,
                   (SELECT COUNT(*) FROM class_subjects cs WHERE cs.class_id = c.id AND cs.term_id = ? AND cs.is_locked = 1) as locked_subjects
            FROM classes c
            JOIN school_levels sl ON sl.id = c.level_id
            WHERE c.academic_year_id = ? 
              AND c.id NOT IN (SELECT class_id FROM report_card_locks WHERE term_id = ? AND is_published = 1)
            HAVING locked_subjects > 0
            ORDER BY sl.sort_order, c.class_name
        ", [$activeTerm['id'], $activeTerm['id'], $activeYear['id'], $activeTerm['id']]);


        // 5. Teacher SBA Commitment Levels
        // Calculate the ratio of entered SBA scores vs expected SBA scores for each teacher's assigned subjects
        $teacherCommitments = DB::query("
            SELECT u.id, u.full_name, u.phone,
                   COUNT(DISTINCT cs.id) as subjects_assigned,
                   -- Expected scores: Sum of students in each assigned class
                   SUM((SELECT COUNT(*) FROM students s WHERE s.current_class_id = cs.class_id AND s.status = 'active')) as expected_entries,
                   -- Actual scores entered by this teacher for this term
                   (SELECT COUNT(*) FROM sba_component_scores scs 
                    JOIN class_subjects inner_cs ON inner_cs.id = scs.class_subject_id
                    WHERE inner_cs.teacher_id = u.id AND scs.term_id = ?) as actual_entries
            FROM users u
            JOIN class_subjects cs ON cs.teacher_id = u.id
            JOIN classes c ON c.id = cs.class_id AND c.academic_year_id = ?
            WHERE u.role = 'teacher' AND u.is_active = 1 AND cs.term_id = ?
            GROUP BY u.id
            ORDER BY actual_entries / NULLIF(SUM((SELECT COUNT(*) FROM students s WHERE s.current_class_id = cs.class_id AND s.status = 'active')), 0) DESC, u.full_name
            LIMIT 5
        ", [$activeTerm['id'], $activeYear['id'], $activeTerm['id']]);
    }
}