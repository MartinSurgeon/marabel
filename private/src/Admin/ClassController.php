<?php
/**
 * Class Controller — Classes & Sections
 * Handles CRUD for class sections (e.g. B1A, B8B, ...)
 */

class ClassController {

    public function handle(): void {
        $uri    = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid request token.');
                $this->back();
            }
            $action = $_POST['_action'] ?? '';
            match ($action) {
                'class_store'  => $this->classStore(),
                'class_delete' => $this->classDelete(),
                default => $this->back(),
            };
        }

        // Prepare data for view
        global $classesList, $levelsList, $yearsList, $teachersList, $activeYearId;

        $activeYear    = DB::queryOne("SELECT id, year_name FROM academic_years WHERE is_active = 1 LIMIT 1");
        $activeYearId  = $activeYear['id'] ?? null;
        $yearsList     = DB::query("SELECT id, year_name FROM academic_years ORDER BY year_name DESC");
        $levelsList    = DB::query("SELECT id, name, code FROM school_levels ORDER BY sort_order");
        $teachersList  = DB::query("SELECT id, full_name FROM users WHERE role = 'teacher' AND is_active = 1 ORDER BY full_name");

        $filterYear = $_GET['year_id'] ?? $activeYearId;
        $classesList = [];
        if ($filterYear) {
            $classesList = DB::query(
                "SELECT c.*, sl.name as level_name, sl.code as level_code,
                        u.full_name as teacher_name,
                        (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'active') as student_count
                 FROM classes c
                 JOIN school_levels sl ON sl.id = c.level_id
                 LEFT JOIN users u ON u.id = c.class_teacher_id
                 WHERE c.academic_year_id = ?
                 ORDER BY sl.sort_order, c.class_name",
                [$filterYear]
            );
        }
    }

    private function classStore(): void {
        $rules = [
            'class_name'       => 'required|max:20',
            'level_id'         => 'required|integer',
            'academic_year_id' => 'required|integer',
        ];
        $v = Validator::make($_POST, $rules);
        if (!empty($v->errors())) {
            Session::flash('error', implode(' ', $v->allErrors()));
            $this->back();
        }

        $data = [
            'level_id'         => (int)$_POST['level_id'],
            'class_name'       => strtoupper(trim($_POST['class_name'])),
            'section'          => strtoupper(trim($_POST['section'] ?? '')),
            'class_teacher_id' => $_POST['class_teacher_id'] ?: null,
            'academic_year_id' => (int)$_POST['academic_year_id'],
        ];

        $id = $_POST['class_id'] ?? null;
        if ($id) {
            DB::execute(
                "UPDATE classes SET level_id=?, class_name=?, section=?, class_teacher_id=?, academic_year_id=? WHERE id=?",
                array_merge(array_values($data), [(int)$id])
            );
            Session::flash('success', "Class '{$data['class_name']}' updated.");
        } else {
            DB::insert(
                "INSERT INTO classes (level_id, class_name, section, class_teacher_id, academic_year_id) VALUES (?,?,?,?,?)",
                array_values($data)
            );
            Session::flash('success', "Class '{$data['class_name']}' created.");
        }
        $this->back();
    }

    private function classDelete(): void {
        $id  = (int)($_POST['class_id'] ?? 0);
        $row = DB::queryOne("SELECT class_name FROM classes WHERE id = ?", [$id]);
        DB::execute("DELETE FROM classes WHERE id = ?", [$id]);
        Session::flash('success', "Class '{$row['class_name']}' deleted.");
        $this->back();
    }

    private function back(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/admin/classes');
        exit;
    }
}
