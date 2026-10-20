<?php
/**
 * Assessment Controller
 * Admin CRUD for Speed Tests & Mock Exams (WAEC-graded, separate from term SBA)
 */

class AssessmentController {

    public const TYPES = [
        'speed_test' => 'Speed Test',
        'mock'       => 'Mock Exam',
    ];

    public function handle(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid request token.');
                $this->redirect();
            }
            $action = $_POST['_action'] ?? '';
            match ($action) {
                'assessment_store'  => $this->assessmentStore(),
                'assessment_delete' => $this->assessmentDelete(),
                'assessment_lock'   => $this->assessmentToggleLock(),
                default => $this->redirect(),
            };
        }

        // Prepare data for view
        global $termsList, $selectedTerm, $assessmentsList, $waecClasses;

        $termsList = DB::query(
            "SELECT t.id, t.name, t.is_active, ay.year_name
             FROM terms t
             JOIN academic_years ay ON ay.id = t.academic_year_id
             ORDER BY ay.year_name DESC, t.term_number DESC"
        );

        $termId = (int)($_GET['term'] ?? 0);
        $selectedTerm = null;
        foreach ($termsList as $t) {
            if (($termId && $t['id'] == $termId) || (!$termId && $t['is_active'])) {
                $selectedTerm = $t;
                break;
            }
        }

        $assessmentsList = [];
        $waecClasses     = [];
        if ($selectedTerm) {
            $assessmentsList = DB::query(
                "SELECT a.*,
                        (SELECT COUNT(*) FROM assessment_scores sc
                         WHERE sc.assessment_id = a.id AND sc.raw_score IS NOT NULL) AS scores_entered
                 FROM assessments a
                 WHERE a.term_id = ?
                 ORDER BY a.assessment_type, COALESCE(a.assessment_date, DATE(a.created_at)), a.id",
                [$selectedTerm['id']]
            );

            // WAEC-graded classes that have subjects this term (results links)
            $waecClasses = DB::query(
                "SELECT DISTINCT c.id, c.class_name, c.section
                 FROM classes c
                 JOIN class_subjects cs ON cs.class_id = c.id AND cs.term_id = ?
                 WHERE c.grading_system = 'waec'
                 ORDER BY c.class_name, c.section",
                [$selectedTerm['id']]
            );
        }
    }

    private function assessmentStore(): void {
        $v = Validator::make($_POST, [
            'title'           => 'required|max:100',
            'term_id'         => 'required|integer',
            'assessment_type' => 'required',
            'max_score'       => 'required|numeric',
        ]);
        if (!empty($v->errors())) {
            Session::flash('error', implode(' ', $v->allErrors()));
            $this->redirect((int)($_POST['term_id'] ?? 0));
        }

        $termId   = (int)$_POST['term_id'];
        $type     = $_POST['assessment_type'];
        $maxScore = round((float)$_POST['max_score'], 2);
        $title    = trim($_POST['title']);
        $date     = trim($_POST['assessment_date'] ?? '') ?: null;

        if (!isset(self::TYPES[$type])) {
            Session::flash('error', 'Invalid assessment type.');
            $this->redirect($termId);
        }
        if ($maxScore <= 0 || $maxScore > 1000) {
            Session::flash('error', 'Maximum score must be between 1 and 1000.');
            $this->redirect($termId);
        }

        $id = (int)($_POST['assessment_id'] ?? 0);
        if ($id) {
            $existing = DB::queryOne("SELECT max_score FROM assessments WHERE id = ?", [$id]);
            if (!$existing) $this->redirect($termId);

            DB::beginTransaction();
            try {
                DB::execute(
                    "UPDATE assessments SET assessment_type=?, title=?, assessment_date=?, max_score=? WHERE id=?",
                    [$type, $title, $date, $maxScore, $id]
                );
                // Max score changed: re-grade every score already recorded
                if ((float)$existing['max_score'] !== $maxScore) {
                    self::regradeScores($id, $maxScore);
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollback();
                Session::flash('error', 'Could not update assessment: ' . $e->getMessage());
                $this->redirect($termId);
            }
            Session::flash('success', "'{$title}' updated.");
        } else {
            DB::insert(
                "INSERT INTO assessments (term_id, assessment_type, title, assessment_date, max_score, created_by)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$termId, $type, $title, $date, $maxScore, Session::userId()]
            );
            Session::flash('success', "'{$title}' created. Teachers can now enter scores.");
        }
        $this->redirect($termId);
    }

    private function assessmentDelete(): void {
        $id  = (int)($_POST['assessment_id'] ?? 0);
        $row = DB::queryOne("SELECT title, term_id FROM assessments WHERE id = ?", [$id]);
        if ($row) {
            // Scores are removed via ON DELETE CASCADE
            DB::execute("DELETE FROM assessments WHERE id = ?", [$id]);
            Session::flash('success', "'{$row['title']}' and all its scores deleted.");
        }
        $this->redirect((int)($row['term_id'] ?? 0));
    }

    private function assessmentToggleLock(): void {
        $id  = (int)($_POST['assessment_id'] ?? 0);
        $row = DB::queryOne("SELECT title, term_id, is_locked FROM assessments WHERE id = ?", [$id]);
        if ($row) {
            DB::execute("UPDATE assessments SET is_locked = NOT is_locked WHERE id = ?", [$id]);
            Session::flash('success', "'{$row['title']}' " . ($row['is_locked'] ? 'unlocked for editing.' : 'locked.'));
        }
        $this->redirect((int)($row['term_id'] ?? 0));
    }

    /**
     * Recompute percentage + WAEC grade for every score of an assessment.
     */
    public static function regradeScores(int $assessmentId, float $maxScore): void {
        $rows = DB::query(
            "SELECT id, raw_score FROM assessment_scores WHERE assessment_id = ? AND raw_score IS NOT NULL",
            [$assessmentId]
        );
        foreach ($rows as $r) {
            $raw = min((float)$r['raw_score'], $maxScore);
            $pct = GradingEngine::toPercentage($raw, $maxScore);
            DB::execute(
                "UPDATE assessment_scores SET raw_score = ?, percentage = ?, grade = ? WHERE id = ?",
                [$raw, $pct, GradingEngine::getProficiencyLevel($pct, 'waec'), $r['id']]
            );
        }
    }

    private function redirect(int $termId = 0): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/admin/assessments' . ($termId ? '?term=' . $termId : ''));
        exit;
    }
}
