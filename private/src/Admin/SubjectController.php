<?php
/**
 * Subject Controller
 * Handles CRUD for academic subjects (level-based)
 */

class SubjectController {

    public function handle(): void {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid request token.');
                $this->redirect();
            }
            $action = $_POST['_action'] ?? '';
            match ($action) {
                'subject_store'  => $this->subjectStore(),
                'subject_delete' => $this->subjectDelete(),
                'subject_toggle' => $this->subjectToggle(),
                default => $this->redirect(),
            };
        }

        // Prepare data for view
        global $subjectsList, $levelsList;
        $levelsList   = DB::query("SELECT id, name, code FROM school_levels ORDER BY sort_order");
        $subjectsList = DB::query(
            "SELECT s.*, sl.name as level_name, sl.code as level_code,
                    (SELECT GROUP_CONCAT(DISTINCT u.full_name SEPARATOR ', ')
                     FROM class_subjects cs
                     JOIN users u ON u.id = cs.teacher_id
                     WHERE cs.subject_id = s.id) as assigned_teachers
             FROM subjects s
             JOIN school_levels sl ON sl.id = s.level_id
             ORDER BY sl.sort_order, s.sort_order, s.subject_name"
        );
    }

    private function subjectStore(): void {
        $rules = [
            'subject_name' => 'required|max:100',
            'level_id'     => 'required|integer',
        ];

        $v = Validator::make($_POST, $rules);
        if (!empty($v->errors())) {
            Session::flash('error', implode(' ', $v->allErrors()));
            $this->redirect();
        }

        $data = [
            'level_id'     => (int)$_POST['level_id'],
            'subject_name' => trim($_POST['subject_name']),
            'subject_code' => strtoupper(trim($_POST['subject_code'] ?? '')),
            'sort_order'   => (int)($_POST['sort_order'] ?? 0),
        ];

        $id = $_POST['subject_id'] ?? null;
        if ($id) {
            DB::execute(
                "UPDATE subjects SET level_id=?, subject_name=?, subject_code=?, sort_order=? WHERE id=?",
                array_merge(array_values($data), [(int)$id])
            );
            Session::flash('success', "Subject '{$data['subject_name']}' updated.");
        } else {
            DB::insert(
                "INSERT INTO subjects (level_id, subject_name, subject_code, sort_order) VALUES (?,?,?,?)",
                array_values($data)
            );
            Session::flash('success', "Subject '{$data['subject_name']}' created.");
        }
        $this->redirect();
    }

    private function subjectDelete(): void {
        $id = (int)($_POST['subject_id'] ?? 0);
        $row = DB::queryOne("SELECT subject_name FROM subjects WHERE id = ?", [$id]);
        if ($row) {
            // Check if scores exist for this subject before deleting
            $exists = DB::queryOne("SELECT id FROM sba_component_scores WHERE class_subject_id IN (SELECT id FROM class_subjects WHERE subject_id = ?) LIMIT 1", [$id]);
            if ($exists) {
                Session::flash('error', "Cannot delete '{$row['subject_name']}' because it has recorded scores.");
            } else {
                DB::execute("DELETE FROM subjects WHERE id = ?", [$id]);
                Session::flash('success', "Subject '{$row['subject_name']}' deleted.");
            }
        }
        $this->redirect();
    }

    private function subjectToggle(): void {
        $id = (int)($_POST['subject_id'] ?? 0);
        DB::execute("UPDATE subjects SET is_active = NOT is_active WHERE id = ?", [$id]);
        Session::flash('success', "Subject status updated.");
        $this->redirect();
    }

    private function redirect(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/admin/subjects');
        exit;
    }
}
