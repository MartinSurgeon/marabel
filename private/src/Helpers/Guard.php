<?php
/**
 * Guard Helper — shared data-integrity checks
 * Uaddara Basic School — SBA Management System
 *
 * One place for the rules every write path must follow:
 *  - never trust a value from the browser that the server can derive
 *  - a student must belong to the class being edited
 *  - nothing on a published report can change until it is unpublished
 */

class Guard {

    /**
     * Parse a score typed by a user.
     * Returns ['ok' => bool, 'value' => ?float, 'message' => string].
     * Empty input means "cleared" (value null); text like "abc" is rejected.
     */
    public static function parseScore(mixed $raw, float $max): array {
        $raw = trim((string)($raw ?? ''));
        if ($raw === '') {
            return ['ok' => true, 'value' => null, 'message' => ''];
        }
        if (!is_numeric($raw)) {
            return ['ok' => false, 'value' => null, 'message' => 'Score must be a number'];
        }
        $value = (float)$raw;
        if ($value < 0 || $value > $max) {
            return ['ok' => false, 'value' => null, 'message' => 'Score must be between 0 and ' . $max];
        }
        return ['ok' => true, 'value' => $value, 'message' => ''];
    }

    /** True if the student is an active member of the class. */
    public static function studentInClass(int $studentId, int $classId): bool {
        return (bool)DB::queryValue(
            "SELECT COUNT(*) FROM students WHERE id = ? AND current_class_id = ? AND status = 'active'",
            [$studentId, $classId]
        );
    }

    /** True if report cards for this class + term are published (and so frozen). */
    public static function isPublished(int $classId, int $termId): bool {
        return (bool)DB::queryValue(
            "SELECT COUNT(*) FROM report_card_locks WHERE class_id = ? AND term_id = ? AND is_published = 1",
            [$classId, $termId]
        );
    }

    /**
     * The class a student belonged to in a given term.
     * Uses recorded results first, so it stays correct after promotion or graduation.
     */
    public static function termClassId(int $studentId, int $termId): ?int {
        $classId = DB::queryValue(
            "SELECT class_id FROM student_aggregates WHERE student_id = ? AND term_id = ? LIMIT 1",
            [$studentId, $termId]
        );
        if ($classId) return (int)$classId;

        // Fall back to any recorded score for that term
        $classId = DB::queryValue(
            "SELECT cs.class_id FROM class_subjects cs
             WHERE cs.term_id = ? AND cs.id IN (
                 SELECT class_subject_id FROM computed_scores      WHERE student_id = ? AND term_id = ?
                 UNION SELECT class_subject_id FROM sba_component_scores WHERE student_id = ? AND term_id = ?
                 UNION SELECT class_subject_id FROM exam_scores          WHERE student_id = ? AND term_id = ?
             ) LIMIT 1",
            [$termId, $studentId, $termId, $studentId, $termId, $studentId, $termId]
        );
        if ($classId) return (int)$classId;

        $current = DB::queryValue("SELECT current_class_id FROM students WHERE id = ?", [$studentId]);
        return $current ? (int)$current : null;
    }

    /** True if a teacher teaches a subject in, or is form teacher of, the class. */
    public static function teacherCanViewClass(int $teacherId, int $classId, int $termId): bool {
        return (bool)DB::queryValue(
            "SELECT (SELECT COUNT(*) FROM class_subjects WHERE class_id = ? AND term_id = ? AND teacher_id = ?)
                  + (SELECT COUNT(*) FROM class_teachers WHERE class_id = ? AND teacher_id = ?)",
            [$classId, $termId, $teacherId, $classId, $teacherId]
        );
    }
}
