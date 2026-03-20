<?php
/**
 * Teacher Score Entry Controller
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';

class ScoreController {

    public function handle(): void {
        Session::requireRole('teacher', 'admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveScore();
            return;
        }

        $this->displayGrid();
    }

    private function displayGrid(): void {
        $csId = (int)($_GET['id'] ?? 0);
        if (!$csId) {
            header('Location: ' . APP_BASE . '/teacher');
            exit;
        }

        // Get Class Subject Details
        $cs = DB::queryOne(
            "SELECT cs.*, c.class_name, c.section, s.subject_name 
             FROM class_subjects cs
             JOIN classes c ON cs.class_id = c.id
             JOIN subjects s ON cs.subject_id = s.id
             WHERE cs.id = ?",
            [$csId]
        );

        if (!$cs) {
            header('Location: ' . APP_BASE . '/teacher');
            exit;
        }

        // Check ownership (unless admin)
        if (Session::role() === 'teacher' && $cs['teacher_id'] != Session::userId()) {
            Session::flash('error', 'You are not assigned to this class/subject.');
            header('Location: ' . APP_BASE . '/teacher');
            exit;
        }

        // Get Students in this class
        $students = DB::query(
            "SELECT id, student_id_number, full_name, surname 
             FROM students 
             WHERE current_class_id = ? AND status = 'active'
             ORDER BY surname ASC, full_name ASC",
            [$cs['class_id']]
        );

        // Get Existing SBA Scores
        $sbaScores = DB::query(
            "SELECT * FROM sba_component_scores WHERE class_subject_id = ? AND term_id = ?",
            [$csId, $cs['term_id']]
        );
        $sbaMap = [];
        foreach ($sbaScores as $s) {
            $sbaMap[$s['student_id']] = $s;
        }

        // Get Existing Exam Scores
        $examScores = DB::query(
            "SELECT * FROM exam_scores WHERE class_subject_id = ? AND term_id = ?",
            [$csId, $cs['term_id']]
        );
        $examMap = [];
        foreach ($examScores as $e) {
            $examMap[$e['student_id']] = $e;
        }

        global $classSub, $studentList, $sbaData, $examData;
        $classSub    = $cs;
        $studentList = $students;
        $sbaData     = $sbaMap;
        $examData    = $examMap;
    }

    private function saveScore(): void {
        // Start output buffering so any stray PHP warnings/notices
        // don't corrupt the JSON response.
        ob_start();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $csId      = (int)($_POST['class_subject_id'] ?? 0);
        $termId    = (int)($_POST['term_id'] ?? 0);
        $field     = $_POST['field'] ?? '';
        $value     = isset($_POST['value']) && $_POST['value'] !== '' ? (float)$_POST['value'] : null;

        if (!$studentId || !$csId || !$termId || !$field) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            return;
        }

        // Check if locked
        $isLocked = DB::queryValue("SELECT is_locked FROM class_subjects WHERE id = ?", [$csId]);
        if ($isLocked) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Records are locked']);
            exit;
        }

        $validFields = ['individual_test', 'group_work', 'class_test', 'project', 'raw_score'];
        if (!in_array($field, $validFields)) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid field']);
            exit;
        }

        // Range check
        if ($value !== null) {
            $max = ($field === 'raw_score') ? 100 : 15;
            if ($value < 0 || $value > $max) {
                ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "Score must be between 0 and $max"]);
                exit;
            }
        }

        try {
            if ($field === 'raw_score') {
                $this->updateExamScore($studentId, $csId, $termId, $value);
            } else {
                $this->updateSbaScore($studentId, $csId, $termId, $field, $value);
            }
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } catch (\PDOException $e) {
            $msg = ($e->getCode() == '23000') ? 'Duplicate record detected' : 'Database error: ' . $e->getMessage();
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        } catch (\Exception $e) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
            exit;
        }
    }

    private function updateSbaScore($studentId, $csId, $termId, $field, $value): void {
        $exists = DB::queryOne(
            "SELECT id FROM sba_component_scores WHERE student_id = ? AND class_subject_id = ? AND term_id = ?",
            [$studentId, $csId, $termId]
        );

        if ($exists) {
            DB::execute(
                "UPDATE sba_component_scores SET $field = ?, updated_at = NOW(), entered_by = ? WHERE id = ?",
                [$value, Session::userId(), $exists['id']]
            );
        } else {
            DB::execute(
                "INSERT INTO sba_component_scores (student_id, class_subject_id, term_id, $field, entered_by) 
                 VALUES (?, ?, ?, ?, ?)",
                [$studentId, $csId, $termId, $value, Session::userId()]
            );
        }

        // Recalculate Totals for this student/subject
        $scores = DB::queryOne(
            "SELECT individual_test, group_work, class_test, project 
             FROM sba_component_scores WHERE student_id = ? AND class_subject_id = ? AND term_id = ?",
            [$studentId, $csId, $termId]
        );
        
        $subTotal = (float)($scores['individual_test'] ?? 0) + 
                    (float)($scores['group_work'] ?? 0) + 
                    (float)($scores['class_test'] ?? 0) + 
                    (float)($scores['project'] ?? 0);
        
        // Scale 60 -> 50
        $classScore = round(($subTotal / 60) * 50, 2);

        DB::execute(
            "UPDATE sba_component_scores SET sub_total = ?, class_score = ? WHERE student_id = ? AND class_subject_id = ? AND term_id = ?",
            [$subTotal, $classScore, $studentId, $csId, $termId]
        );
    }

    private function updateExamScore($studentId, $csId, $termId, $value): void {
        $exists = DB::queryOne(
            "SELECT id FROM exam_scores WHERE student_id = ? AND class_subject_id = ? AND term_id = ?",
            [$studentId, $csId, $termId]
        );

        $examScaled = ($value === null) ? null : round(($value / 100) * 50, 2);

        if ($exists) {
            DB::execute(
                "UPDATE exam_scores SET raw_score = ?, exam_score = ?, updated_at = NOW(), entered_by = ? WHERE id = ?",
                [$value, $examScaled, Session::userId(), $exists['id']]
            );
        } else {
            DB::execute(
                "INSERT INTO exam_scores (student_id, class_subject_id, term_id, raw_score, exam_score, entered_by) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$studentId, $csId, $termId, $value, $examScaled, Session::userId()]
            );
        }
    }
}
