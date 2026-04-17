<?php
/**
 * Bulk Print Controller
 * Uaddara Basic School — SBA Management System
 *
 * Serves a single HTML page containing all report cards for a class
 * with page breaks, for bulk printing by administrators.
 * URL:  /admin/publish/print?class_id={id}&term_id={id}
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';

class BulkPrintController {

    public function handle(): void {
        Session::requireAuth();
        
        $role    = Session::role();
        $classId = (int)($_GET['class_id'] ?? 0);
        $termId  = (int)($_GET['term_id']  ?? 0);

        if (!$classId || !$termId) {
            $this->abort('Missing class or term parameter.');
        }

        // Only Admin or the Class Teacher can bulk print
        if ($role === 'teacher') {
            $isClassTeacher = DB::queryOne(
                "SELECT 1 FROM class_teachers WHERE class_id = ? AND teacher_id = ?",
                [$classId, Session::userId()]
            );
            if (!$isClassTeacher) {
                $this->abort('You are not authorised to bulk print this class.');
            }
        } elseif ($role !== 'admin') {
            $this->abort('You are not authorised to bulk print reports.');
        }

        // 1. Check Publication Status
        $publishedRecord = DB::queryOne(
            "SELECT is_published FROM report_card_locks WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );
        $isPublished = (bool)($publishedRecord['is_published'] ?? false);
        
        if (!$isPublished && $role !== 'admin') {
             $this->abort('Results for this class have not been published yet.');
        }

        // 2. Class Info
        $classInfo = DB::queryOne(
            "SELECT c.id as class_id, c.class_name, c.section, c.grading_system,
                    sl.name as level_name, sl.code as level_code
             FROM classes c
             LEFT JOIN school_levels sl ON sl.id = c.level_id
             WHERE c.id = ?",
            [$classId]
        );
        if (!$classInfo) $this->abort('Class not found.');

        // 3. Term Info
        $term = DB::queryOne(
            "SELECT t.id, t.name, t.term_number, t.total_school_days,
                    t.next_term_begins, t.end_date,
                    ay.year_name
             FROM terms t
             JOIN academic_years ay ON ay.id = t.academic_year_id
             WHERE t.id = ?",
            [$termId]
        );
        if (!$term) $this->abort('Term not found.');

        // 4. Get active students in this class
        $students = DB::query(
            "SELECT id, student_id_number, full_name, surname, gender,
                    date_of_birth, photo_path
             FROM students 
             WHERE current_class_id = ? AND status = 'active'
             ORDER BY surname ASC, full_name ASC",
            [$classId]
        );

        if (empty($students)) {
            $this->abort('No students found in this class.');
        }

        $studentIds = array_column($students, 'id');
        $placeholders = str_repeat('?,', count($studentIds) - 1) . '?';

        // 5. Bulk fetch all scores, aggregates, remarks, attendance
        $scoresRaw = DB::query(
            "SELECT cs2.student_id, sub.subject_name, sub.subject_code,
                    cs2.class_score, cs2.exam_score, cs2.overall_total,
                    cs2.proficiency_level, cs2.subject_position
             FROM computed_scores cs2
             JOIN class_subjects cs ON cs.id = cs2.class_subject_id
             JOIN subjects sub ON sub.id = cs.subject_id
             WHERE cs.class_id = ? AND cs2.term_id = ?
             ORDER BY cs2.student_id, sub.sort_order, sub.subject_name",
            [$classId, $termId]
        );

        $aggregatesRaw = DB::query(
            "SELECT student_id, aggregate_score, class_position, number_of_subjects, aggregate_grade
             FROM student_aggregates
             WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );

        $remarksRaw = DB::query(
            "SELECT student_id, conduct_character, attitude, teacher_remark, headmaster_remark
             FROM student_remarks
             WHERE term_id = ? AND student_id IN ($placeholders)",
            array_merge([$termId], $studentIds)
        );

        $attendanceRaw = DB::query(
            "SELECT student_id, days_present
             FROM attendance
             WHERE term_id = ? AND student_id IN ($placeholders)",
            array_merge([$termId], $studentIds)
        );

        // Group data by student_id
        $scoresByStudent     = [];
        $aggregatesByStudent = [];
        $remarksByStudent    = [];
        $attendanceByStudent = [];

        foreach ($scoresRaw as $s) {
            $scoresByStudent[$s['student_id']][] = $s;
        }
        foreach ($aggregatesRaw as $a) {
            $aggregatesByStudent[$a['student_id']] = $a;
        }
        foreach ($remarksRaw as $r) {
            $remarksByStudent[$r['student_id']] = $r;
        }
        foreach ($attendanceRaw as $a) {
            $attendanceByStudent[$a['student_id']] = $a;
        }

        // Class Teacher
        $classSize = count($students);
        $classTeacher = DB::queryOne(
            "SELECT u.full_name
             FROM class_teachers ct
             JOIN users u ON u.id = ct.teacher_id
             WHERE ct.class_id = ?
             LIMIT 1",
            [$classId]
        );

        global $bp_classInfo, $bp_term, $bp_students, $bp_scores, $bp_aggregates,
               $bp_remarks, $bp_attendance, $bp_classSize, $bp_classTeacher, $bp_isPublished;

        $bp_classInfo    = $classInfo;
        $bp_term         = $term;
        $bp_students     = $students;
        $bp_scores       = $scoresByStudent;
        $bp_aggregates   = $aggregatesByStudent;
        $bp_remarks      = $remarksByStudent;
        $bp_attendance   = $attendanceByStudent;
        $bp_classSize    = $classSize;
        $bp_classTeacher = $classTeacher;
        $bp_isPublished  = $isPublished;
    }

    private function abort(string $message): never {
        http_response_code(403);
        include ROOT_PATH . '/templates/errors/404.php';
        exit;
    }
}
