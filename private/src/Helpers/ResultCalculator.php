<?php
/**
 * Result Calculator Helper
 * Uaddara Basic School — SBA Management System
 */

require_once __DIR__ . '/../Engine/GradingEngine.php';

class ResultCalculator {

    /**
     * Computes and saves computed_scores and student_aggregates for an entire class.
     */
    public static function compute(int $classId, int $termId): void {
        // 0. Fetch class grading system
        $classMeta = DB::queryOne("SELECT grading_system FROM classes WHERE id = ?", [$classId]);
        $gradingSystem = $classMeta['grading_system'] ?? 'proficiency';

        // 1. Fetch students and subjects
        $students = DB::query(
            "SELECT id FROM students WHERE current_class_id = ? AND status = 'active'",
            [$classId]
        );
        $subjects = DB::query(
            "SELECT id, subject_id FROM class_subjects WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );

        if (empty($students) || empty($subjects)) return;

        $studentIds = array_column($students,  'id');
        $csIds      = array_column($subjects,  'id');

        $sIn  = implode(',', array_fill(0, count($studentIds), '?'));
        $csIn = implode(',', array_fill(0, count($csIds),      '?'));

        // 2. Batch-fetch all SBA component scores for these students/class_subjects
        $compRows = DB::query(
            "SELECT student_id, class_subject_id,
                    individual_test, group_work, class_test, project
             FROM sba_component_scores
             WHERE student_id IN ({$sIn}) AND class_subject_id IN ({$csIn}) AND term_id = ?",
            [...$studentIds, ...$csIds, $termId]
        );
        // Index: [student_id][class_subject_id]
        $compMap = [];
        foreach ($compRows as $row) {
            $compMap[$row['student_id']][$row['class_subject_id']] = $row;
        }

        // 3. Batch-fetch all exam scores
        $examRows = DB::query(
            "SELECT student_id, class_subject_id, raw_score
             FROM exam_scores
             WHERE student_id IN ({$sIn}) AND class_subject_id IN ({$csIn}) AND term_id = ?",
            [...$studentIds, ...$csIds, $termId]
        );
        $examMap = [];
        foreach ($examRows as $row) {
            $examMap[$row['student_id']][$row['class_subject_id']] = $row;
        }

        // 4. Compute scores for each student × subject
        $computedRows = []; // [class_subject_id => [student_id => result]]

        foreach ($students as $student) {
            $sid            = $student['id'];
            $totalAggregate = 0.0;
            $subjectCount   = 0;

            foreach ($subjects as $cs) {
                $csId = $cs['id'];
                $comp = $compMap[$sid][$csId] ?? null;
                $exam = $examMap[$sid][$csId] ?? null;

                if (!$comp && !$exam) continue; // No data entered

                $result = GradingEngine::computeFull(
                    (float)($comp['individual_test'] ?? 0),
                    (float)($comp['group_work']      ?? 0),
                    (float)($comp['class_test']      ?? 0),
                    (float)($comp['project']         ?? 0),
                    (float)($exam['raw_score']       ?? 0),
                    $gradingSystem
                );

                // Upsert into computed_scores
                DB::execute(
                    "INSERT INTO computed_scores
                        (student_id, class_subject_id, term_id, class_score, exam_score, overall_total, proficiency_level)
                     VALUES (?, ?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        class_score=?, exam_score=?, overall_total=?, proficiency_level=?",
                    [
                        $sid, $csId, $termId,
                        $result['class_score'], $result['exam_score'],
                        $result['overall_total'], $result['proficiency_level'],
                        $result['class_score'], $result['exam_score'],
                        $result['overall_total'], $result['proficiency_level'],
                    ]
                );

                $computedRows[$csId][$sid] = $result['overall_total'];
                $totalAggregate           += $result['overall_total'];
                $subjectCount++;
            }

            // Upsert aggregate
            if ($subjectCount > 0) {
                DB::execute(
                    "INSERT INTO student_aggregates
                        (student_id, class_id, term_id, aggregate_score, number_of_subjects)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        aggregate_score=?, number_of_subjects=?",
                    [
                        $sid, $classId, $termId, $totalAggregate, $subjectCount,
                        $totalAggregate, $subjectCount,
                    ]
                );
            }
        }

        // 5. Subject Positions
        foreach ($subjects as $cs) {
            $csId        = $cs['id'];
            $subjectData = $computedRows[$csId] ?? [];
            if (empty($subjectData)) continue;

            $arr = array_map(
                fn($sid, $ot) => ['student_id' => $sid, 'overall_total' => $ot],
                array_keys($subjectData), $subjectData
            );

            $ranked = GradingEngine::computeSubjectPositions($arr);
            foreach ($ranked as $rs) {
                DB::execute(
                    "UPDATE computed_scores SET subject_position = ?
                     WHERE student_id = ? AND class_subject_id = ? AND term_id = ?",
                    [$rs['subject_position'], $rs['student_id'], $csId, $termId]
                );
            }
        }

        // 6. Class Positions
        $aggregates = DB::query(
            "SELECT student_id, aggregate_score AS aggregate
             FROM student_aggregates WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );

        if (!empty($aggregates)) {
            $ranked = GradingEngine::computeClassPositions($aggregates);
            foreach ($ranked as $ra) {
                DB::execute(
                    "UPDATE student_aggregates SET class_position = ?
                     WHERE student_id = ? AND class_id = ? AND term_id = ?",
                    [$ra['position'], $ra['student_id'], $classId, $termId]
                );
            }
        }
    }
}
