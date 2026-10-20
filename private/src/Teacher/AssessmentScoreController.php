<?php
/**
 * Teacher Assessment Score Entry Controller
 * Records Speed Test / Mock Exam scores for WAEC-graded classes.
 */

require_once PRIVATE_PATH . '/src/Admin/AssessmentController.php';

class AssessmentScoreController
{
    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveScore();
        }

        global $activeTerm, $assessmentList, $selectedAssessment;
        $activeTerm = DB::queryOne(
            "SELECT t.*, ay.year_name FROM terms t
             JOIN academic_years ay ON t.academic_year_id = ay.id
             WHERE t.is_active = 1 LIMIT 1"
        );

        $assessmentList = $activeTerm ? DB::query(
            "SELECT * FROM assessments WHERE term_id = ?
             ORDER BY assessment_type, COALESCE(assessment_date, DATE(created_at)), id",
            [$activeTerm['id']]
        ) : [];

        $assessmentId = (int)($_GET['assessment'] ?? 0);
        $selectedAssessment = null;
        foreach ($assessmentList as $a) {
            if ($a['id'] == $assessmentId) $selectedAssessment = $a;
        }
        // Default to the most recent assessment
        if (!$selectedAssessment && !empty($assessmentList)) {
            $selectedAssessment = end($assessmentList);
        }

        if (!$selectedAssessment) return;

        $csId = (int)($_GET['cs'] ?? 0);
        if ($csId) {
            $this->displayGrid($selectedAssessment, $csId);
        } else {
            $this->displaySelection($selectedAssessment);
        }
    }

    /**
     * Load a class-subject the current user may enter assessment scores for.
     */
    private function loadClassSubject(int $csId, array $assessment): ?array
    {
        $cs = DB::queryOne(
            "SELECT cs.*, c.class_name, c.section, c.grading_system, s.subject_name
             FROM class_subjects cs
             JOIN classes c  ON cs.class_id = c.id
             JOIN subjects s ON cs.subject_id = s.id
             WHERE cs.id = ?",
            [$csId]
        );

        if (!$cs || $cs['grading_system'] !== 'waec' || $cs['term_id'] != $assessment['term_id']) {
            return null;
        }
        if (Session::role() === 'teacher' && $cs['teacher_id'] != Session::userId()) {
            return null;
        }
        return $cs;
    }

    private function displaySelection(array $assessment): void
    {
        $isAdmin = (Session::role() === 'admin');
        $params  = [$assessment['id'], $assessment['term_id']];
        if (!$isAdmin) $params[] = Session::userId();

        global $assignedBundles;
        $assignedBundles = DB::query(
            "SELECT cs.id AS class_subject_id, c.id AS class_id, c.class_name, c.section,
                    s.subject_name, u.full_name AS teacher_name,
                    (SELECT COUNT(*) FROM students st WHERE st.current_class_id = c.id AND st.status = 'active') AS student_count,
                    (SELECT COUNT(*) FROM assessment_scores sc
                     JOIN students st2 ON sc.student_id = st2.id AND st2.current_class_id = c.id AND st2.status = 'active'
                     WHERE sc.class_subject_id = cs.id AND sc.assessment_id = ? AND sc.raw_score IS NOT NULL) AS entered_count
             FROM class_subjects cs
             JOIN classes c  ON cs.class_id = c.id
             JOIN subjects s ON cs.subject_id = s.id
             LEFT JOIN users u ON cs.teacher_id = u.id
             WHERE cs.term_id = ? AND c.grading_system = 'waec'"
             . ($isAdmin ? '' : ' AND cs.teacher_id = ?') .
            " ORDER BY c.class_name, c.section, s.sort_order",
            $params
        );
    }

    private function displayGrid(array $assessment, int $csId): void
    {
        $cs = $this->loadClassSubject($csId, $assessment);
        if (!$cs) {
            Session::flash('error', 'You cannot enter scores for this class/subject.');
            header('Location: ' . APP_BASE . '/teacher/assessments?assessment=' . $assessment['id']);
            exit;
        }

        global $classSub, $studentList, $scoreData;
        $classSub    = $cs;
        $studentList = DB::query(
            "SELECT id, student_id_number, full_name, surname, gender
             FROM students
             WHERE current_class_id = ? AND status = 'active'
             ORDER BY gender ASC, surname ASC, full_name ASC",
            [$cs['class_id']]
        );

        $scoreData = [];
        $rows = DB::query(
            "SELECT student_id, raw_score, percentage, grade FROM assessment_scores
             WHERE assessment_id = ? AND class_subject_id = ?",
            [$assessment['id'], $csId]
        );
        foreach ($rows as $r) {
            $scoreData[$r['student_id']] = $r;
        }
    }

    private function saveScore(): never
    {
        // Buffer so stray warnings don't corrupt the JSON response
        ob_start();

        $respond = function (array $payload): never {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($payload);
            exit;
        };

        if (!CSRF::verify()) {
            $respond(['success' => false, 'message' => 'Session expired. Please reload the page.']);
        }

        $assessmentId = (int)($_POST['assessment_id'] ?? 0);
        $csId         = (int)($_POST['class_subject_id'] ?? 0);
        $studentId    = (int)($_POST['student_id'] ?? 0);
        $rawValue     = trim((string)($_POST['value'] ?? ''));
        $value        = $rawValue !== '' ? (float)$rawValue : null;

        // Reject text like "abc" instead of silently saving it as 0
        if ($rawValue !== '' && !is_numeric($rawValue)) {
            $respond(['success' => false, 'message' => 'Score must be a number']);
        }

        $assessment = DB::queryOne("SELECT * FROM assessments WHERE id = ?", [$assessmentId]);
        if (!$assessment || !$csId || !$studentId) {
            $respond(['success' => false, 'message' => 'Missing parameters']);
        }
        if ($assessment['is_locked']) {
            $respond(['success' => false, 'message' => 'This assessment is locked']);
        }

        $cs = $this->loadClassSubject($csId, $assessment);
        if (!$cs) {
            $respond(['success' => false, 'message' => 'Not permitted for this class/subject']);
        }

        $inClass = DB::queryValue(
            "SELECT COUNT(*) FROM students WHERE id = ? AND current_class_id = ? AND status = 'active'",
            [$studentId, $cs['class_id']]
        );
        if (!$inClass) {
            $respond(['success' => false, 'message' => 'Student is not in this class']);
        }

        $max = (float)$assessment['max_score'];
        if ($value !== null && ($value < 0 || $value > $max)) {
            $respond(['success' => false, 'message' => 'Score must be between 0 and ' . $max]);
        }

        try {
            if ($value === null) {
                // Cleared field: remove the record instead of storing an empty row
                DB::execute(
                    "DELETE FROM assessment_scores WHERE assessment_id = ? AND student_id = ? AND class_subject_id = ?",
                    [$assessmentId, $studentId, $csId]
                );
                $respond(['success' => true, 'percentage' => null, 'grade' => null]);
            }

            $pct   = GradingEngine::toPercentage($value, $max);
            $grade = GradingEngine::getProficiencyLevel($pct, 'waec');

            DB::execute(
                "INSERT INTO assessment_scores
                    (assessment_id, student_id, class_subject_id, raw_score, percentage, grade, entered_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    raw_score = VALUES(raw_score), percentage = VALUES(percentage),
                    grade = VALUES(grade), entered_by = VALUES(entered_by)",
                [$assessmentId, $studentId, $csId, $value, $pct, $grade, Session::userId()]
            );

            $respond([
                'success'    => true,
                'percentage' => $pct,
                'grade'      => $grade,
                'label'      => GradingEngine::getProficiencyLabel($grade, 'waec'),
            ]);
        } catch (\PDOException $e) {
            $respond(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
