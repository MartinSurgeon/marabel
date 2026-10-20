<?php
/**
 * Assessment Results Controller
 * Class broadsheet for a Speed Test / Mock Exam:
 * WAEC grade per subject, Best-6 aggregate and class position.
 */

require_once PRIVATE_PATH . '/src/Admin/AssessmentController.php';

class AssessmentResultsController
{
    public function handle(): void
    {
        $base         = defined('APP_BASE') ? APP_BASE : '';
        $assessmentId = (int)($_GET['assessment'] ?? 0);
        $classId      = (int)($_GET['class'] ?? 0);

        $assessment = DB::queryOne(
            "SELECT a.*, t.name AS term_name, ay.year_name
             FROM assessments a
             JOIN terms t ON t.id = a.term_id
             JOIN academic_years ay ON ay.id = t.academic_year_id
             WHERE a.id = ?",
            [$assessmentId]
        );
        $class = DB::queryOne(
            "SELECT id, class_name, section, grading_system FROM classes WHERE id = ?",
            [$classId]
        );

        if (!$assessment || !$class || $class['grading_system'] !== 'waec') {
            Session::flash('error', 'Results not found for that assessment/class.');
            header('Location: ' . $base . (Session::role() === 'admin' ? '/admin/assessments' : '/teacher/assessments'));
            exit;
        }

        // Teachers may only view classes they teach or manage
        if (Session::role() === 'teacher') {
            $allowed = DB::queryValue(
                "SELECT (SELECT COUNT(*) FROM class_subjects WHERE class_id = ? AND term_id = ? AND teacher_id = ?)
                      + (SELECT COUNT(*) FROM class_teachers WHERE class_id = ? AND teacher_id = ?)",
                [$classId, $assessment['term_id'], Session::userId(), $classId, Session::userId()]
            );
            if (!$allowed) {
                Session::flash('error', 'You are not assigned to this class.');
                header('Location: ' . $base . '/teacher/assessments');
                exit;
            }
        }

        $subjects = DB::query(
            "SELECT cs.id, s.subject_name, s.subject_code
             FROM class_subjects cs
             JOIN subjects s ON s.id = cs.subject_id
             WHERE cs.class_id = ? AND cs.term_id = ?
             ORDER BY s.sort_order, s.subject_name",
            [$classId, $assessment['term_id']]
        );

        $students = DB::query(
            "SELECT id, student_id_number, full_name, gender
             FROM students
             WHERE current_class_id = ? AND status = 'active'
             ORDER BY surname, full_name",
            [$classId]
        );

        // [student_id][class_subject_id] => score row
        $scoreMap = [];
        if (!empty($subjects)) {
            $csIds = array_column($subjects, 'id');
            $in    = implode(',', array_fill(0, count($csIds), '?'));
            $rows  = DB::query(
                "SELECT student_id, class_subject_id, raw_score, percentage, grade
                 FROM assessment_scores
                 WHERE assessment_id = ? AND class_subject_id IN ({$in}) AND raw_score IS NOT NULL",
                [$assessmentId, ...$csIds]
            );
            foreach ($rows as $r) {
                $scoreMap[$r['student_id']][$r['class_subject_id']] = $r;
            }
        }

        // Per-student summary
        $results = [];
        foreach ($students as $st) {
            $grades = [];
            $pctSum = 0;
            foreach ($subjects as $sub) {
                $sc = $scoreMap[$st['id']][$sub['id']] ?? null;
                if ($sc) {
                    $grades[$sub['subject_name']] = (int)$sc['grade'];
                    $pctSum += (float)$sc['percentage'];
                }
            }
            $count = count($grades);
            $results[] = [
                'student'   => $st,
                'scores'    => $scoreMap[$st['id']] ?? [],
                'count'     => $count,
                'average'   => $count ? round($pctSum / $count, 1) : null,
                'aggregate' => $count ? GradingEngine::computeWaecAggregate($grades) : null,
                'position'  => null,
            ];
        }

        // Rank by Best-6 aggregate (lower is better) when any student has one,
        // otherwise (e.g. single-subject speed test) by average percentage.
        $rankByAggregate = (bool)array_filter($results, fn($r) => $r['aggregate'] !== null);
        $rankKey         = $rankByAggregate ? 'aggregate' : 'average';

        $ranked = array_filter($results, fn($r) => $r[$rankKey] !== null);
        uasort($ranked, fn($x, $y) => $rankByAggregate
            ? $x['aggregate'] <=> $y['aggregate']
            : $y['average']   <=> $x['average']);

        $position = 0;
        $seen     = 0;
        $last     = null;
        foreach ($ranked as $idx => $r) {
            $seen++;
            if ($r[$rankKey] !== $last) {
                $position = $seen;
                $last     = $r[$rankKey];
            }
            $results[$idx]['position'] = $position;
        }

        // Ranked students first, then unranked (no scores) alphabetically
        usort($results, fn($x, $y) =>
            [$x['position'] === null, $x['position']] <=> [$y['position'] === null, $y['position']]);

        // Subject averages
        $subjectStats = [];
        foreach ($subjects as $sub) {
            $vals = [];
            foreach ($results as $r) {
                if (isset($r['scores'][$sub['id']])) $vals[] = (float)$r['scores'][$sub['id']]['percentage'];
            }
            $subjectStats[$sub['id']] = [
                'count'   => count($vals),
                'average' => $vals ? round(array_sum($vals) / count($vals), 1) : null,
            ];
        }

        // Other assessments in the same term for quick switching
        $siblings = DB::query(
            "SELECT id, title, assessment_type FROM assessments WHERE term_id = ?
             ORDER BY assessment_type, COALESCE(assessment_date, DATE(created_at)), id",
            [$assessment['term_id']]
        );

        global $rsAssessment, $rsClass, $rsSubjects, $rsResults, $rsSubjectStats, $rsRankByAggregate, $rsSiblings;
        $rsAssessment      = $assessment;
        $rsClass           = $class;
        $rsSubjects        = $subjects;
        $rsResults         = $results;
        $rsSubjectStats    = $subjectStats;
        $rsRankByAggregate = $rankByAggregate;
        $rsSiblings        = $siblings;
    }
}
