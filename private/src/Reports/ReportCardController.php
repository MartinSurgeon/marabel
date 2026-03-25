<?php
/**
 * Report Card Controller
 * Uaddara Basic School — SBA Management System
 *
 * Serves individual student report cards.
 * Access: admin, teacher (any term), parent/student (published only).
 * URL:  /report?student={id}&term={id}
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';
require_once PRIVATE_PATH . '/src/Helpers/ResultCalculator.php';

class ReportCardController {

    public function handle(): void {
        Session::requireAuth();

        $role      = Session::role();
        $studentId = (int)($_GET['student'] ?? 0);
        $termId    = (int)($_GET['term']    ?? 0);

        if (!$studentId || !$termId) {
            $this->abort('Missing student or term parameter.');
        }

        // ── 1. Student info ────────────────────────────────────────────
        $student = DB::queryOne(
            "SELECT s.id, s.student_id_number, s.full_name, s.surname, s.gender,
                    s.date_of_birth, s.photo_path,
                    c.class_name, c.section, c.id as class_id, c.grading_system,
                    sl.name as level_name, sl.code as level_code
             FROM students s
             LEFT JOIN classes c  ON c.id = s.current_class_id
             LEFT JOIN school_levels sl ON sl.id = c.level_id
             WHERE s.id = ? AND s.status = 'active'",
            [$studentId]
        );

        if (!$student) {
            $this->abort('Student not found.');
        }

        // ── 2. Term info ───────────────────────────────────────────────
        $term = DB::queryOne(
            "SELECT t.id, t.name, t.term_number, t.total_school_days,
                    t.next_term_begins, t.end_date,
                    ay.year_name
             FROM terms t
             JOIN academic_years ay ON ay.id = t.academic_year_id
             WHERE t.id = ?",
            [$termId]
        );

        if (!$term) {
            $this->abort('Term not found.');
        }

        // ── 3. Access control: parents/students can only see published results ──
        // ── 3. Publication Status (Draft check) ───────────────────────
        $publishedRecord = DB::queryOne(
            "SELECT is_published FROM report_card_locks
             WHERE class_id = ? AND term_id = ?",
            [$student['class_id'], $termId]
        );
        $isPublished = (bool)($publishedRecord['is_published'] ?? false);

        if (in_array($role, ['parent', 'student'])) {
            if (!$isPublished) {
                $this->abort('Results for this term have not been published yet.');
            }

            // Parent extra check: may only view their own children
            if ($role === 'parent') {
                $linked = DB::queryOne(
                    "SELECT id FROM student_parents WHERE student_id = ? AND parent_user_id = ?",
                    [$studentId, Session::userId()]
                );
                if (!$linked) {
                    $this->abort('You are not authorised to view this report card.');
                }
            }

            // Student extra check: may only view their own record
            if ($role === 'student' && Session::userId() !== $studentId) {
                $this->abort('You are not authorised to view this report card.');
            }
        }

        // ── 3.5. On-the-fly computation for Previews (Teachers/Admins) ──
        if (in_array($role, ['admin', 'teacher'])) {
            ResultCalculator::compute($student['class_id'], $termId);
        }

        // ── 4. Subject Scores ──────────────────────────────────────────

        $scores = DB::query(
            "SELECT sub.subject_name, sub.subject_code,
                    cs2.class_score, cs2.exam_score, cs2.overall_total,
                    cs2.proficiency_level, cs2.subject_position
             FROM computed_scores cs2
             JOIN class_subjects cs ON cs.id = cs2.class_subject_id
             JOIN subjects sub ON sub.id = cs.subject_id
             WHERE cs2.student_id = ? AND cs2.term_id = ?
             ORDER BY sub.sort_order, sub.subject_name",
            [$studentId, $termId]
        );

        // ── 5. Aggregate / Class Position ─────────────────────────────
        $aggregate = DB::queryOne(
            "SELECT aggregate_score, class_position, number_of_subjects, aggregate_grade
             FROM student_aggregates
             WHERE student_id = ? AND term_id = ?",
            [$studentId, $termId]
        );

        // Class size (for context)
        $classSize = DB::queryOne(
            "SELECT COUNT(*) as cnt FROM student_aggregates WHERE class_id = ? AND term_id = ?",
            [$student['class_id'], $termId]
        )['cnt'] ?? 0;

        // ── 6. Remarks ────────────────────────────────────────────────
        $remarks = DB::queryOne(
            "SELECT conduct_character, attitude, teacher_remark, headmaster_remark,
                    conduct_remark, interest_remark, attitude_remark
             FROM student_remarks
             WHERE student_id = ? AND term_id = ?",
            [$studentId, $termId]
        );

        // ── 7. Attendance ─────────────────────────────────────────────
        $attendance = DB::queryOne(
            "SELECT days_present FROM attendance
             WHERE student_id = ? AND term_id = ?",
            [$studentId, $termId]
        );

        // ── 8. Class teacher name (for signature area) ─────────────────
        $classTeacher = DB::queryOne(
            "SELECT u.full_name
             FROM class_teachers ct
             JOIN users u ON u.id = ct.teacher_id
             WHERE ct.class_id = ?
             LIMIT 1",
            [$student['class_id']]
        );

        // ── 9. Pass globals to template ───────────────────────────────
        global $rc_student, $rc_term, $rc_scores, $rc_aggregate,
               $rc_classSize, $rc_remarks, $rc_attendance, $rc_classTeacher, $rc_isPublished, $rc_gradingSystem;

        $rc_student      = $student;
        $rc_term         = $term;
        $rc_scores       = $scores;
        $rc_aggregate    = $aggregate;
        $rc_classSize    = (int)$classSize;
        $rc_remarks      = $remarks;
        $rc_attendance   = $attendance;
        $rc_classTeacher = $classTeacher;
        $rc_isPublished  = $isPublished;
        $rc_gradingSystem = $student['grading_system'] ?? 'proficiency';
    }


    private function abort(string $message): never {
        http_response_code(403);
        include ROOT_PATH . '/templates/errors/404.php';
        exit;
    }
}
