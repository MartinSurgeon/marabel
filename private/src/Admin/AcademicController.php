<?php
/**
 * Academic Controller — Academic Years + Terms
 * Handles: list / store / update / delete / set-active for years & terms
 */

class AcademicController {

    public function handle(): void {
        $uri    = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid request token. Please try again.');
                $this->back();
            }
            $action = $_POST['_action'] ?? '';
            match ($action) {
                'year_store'    => $this->yearStore(),
                'year_delete'   => $this->yearDelete(),
                'year_activate' => $this->yearActivate(),
                'term_store'    => $this->termStore(),
                'term_delete'   => $this->termDelete(),
                'term_activate' => $this->termActivate(),
                default => $this->back(),
            };
        }
        // GET handled by template via globals set below
        global $academicYears, $activeTerm;
        $academicYears = DB::query(
            "SELECT ay.*, COUNT(t.id) as term_count
             FROM academic_years ay
             LEFT JOIN terms t ON t.academic_year_id = ay.id
             GROUP BY ay.id
             ORDER BY ay.year_name DESC"
        );
        $activeTerm = null;
    }

    // ── Year ───────────────────────────────────────────────────────────
    private function yearStore(): void {
        $rules = ['year_name' => 'required|max:20'];
        $v = Validator::make($_POST, $rules);
        if (!empty($v->errors())) {
            Session::flash('error', implode(' ', $v->allErrors()));
            $this->back();
        }
        $name = trim($_POST['year_name']);
        $id   = $_POST['year_id'] ?? null;
        try {
            if ($id) {
                DB::execute("UPDATE academic_years SET year_name = ? WHERE id = ?", [$name, $id]);
                Session::flash('success', "Academic year '{$name}' updated.");
            } else {
                DB::insert("INSERT INTO academic_years (year_name) VALUES (?)", [$name]);
                Session::flash('success', "Academic year '{$name}' created.");
            }
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                Session::flash('error', "Academic year '{$name}' already exists.");
            } else {
                Session::flash('error', 'An error occurred. Please try again.');
            }
        }
        $this->back();
    }

    private function yearDelete(): void {
        $id = (int)($_POST['year_id'] ?? 0);
        if (!$id) $this->back();
        $row = DB::queryOne("SELECT year_name FROM academic_years WHERE id = ?", [$id]);
        DB::execute("DELETE FROM academic_years WHERE id = ?", [$id]);
        Session::flash('success', "Academic year '{$row['year_name']}' deleted.");
        $this->back();
    }

    private function yearActivate(): void {
        $id = (int)($_POST['year_id'] ?? 0);
        if (!$id) $this->back();
        DB::execute("UPDATE academic_years SET is_active = 0");
        DB::execute("UPDATE academic_years SET is_active = 1 WHERE id = ?", [$id]);
        $row = DB::queryOne("SELECT year_name FROM academic_years WHERE id = ?", [$id]);
        Session::flash('success', "'{$row['year_name']}' is now the active academic year.");
        Session::updateActiveTerm();
        Notification::send(null, "Academic Year Activated", "'{$row['year_name']}' has been set as the active academic year.", 'success', '/admin/years');
        $this->back();
    }

    // ── Term ───────────────────────────────────────────────────────────
    private function termStore(): void {
        $rules = [
            'academic_year_id' => 'required|integer',
            'term_number'      => 'required|integer|min_val:1|max_val:3',
            'name'             => 'required|max:50',
            'total_school_days'=> 'required|integer|min_val:1|max_val:366',
        ];
        $v = Validator::make($_POST, $rules);
        if (!empty($v->errors())) {
            Session::flash('error', implode(' ', $v->allErrors()));
            $this->back();
        }

        $data = [
            'year_id'     => (int)$_POST['academic_year_id'],
            'term_num'    => (int)$_POST['term_number'],
            'name'        => trim($_POST['name']),
            'start_date'  => $_POST['start_date']  ?: null,
            'end_date'    => $_POST['end_date']    ?: null,
            'next_begins' => $_POST['next_term_begins'] ?: null,
            'days'        => (int)$_POST['total_school_days'],
        ];

        $id = $_POST['term_id'] ?? null;
        if ($id) {
            DB::execute(
                "UPDATE terms SET academic_year_id=?, term_number=?, name=?,
                 start_date=?, end_date=?, next_term_begins=?, total_school_days=?
                 WHERE id=?",
                [
                    $data['year_id'], $data['term_num'], $data['name'],
                    $data['start_date'], $data['end_date'], $data['next_begins'],
                    $data['days'], (int)$id
                ]
            );
            Session::flash('success', "Term '{$data['name']}' updated.");
        } else {
            DB::insert(
                "INSERT INTO terms (academic_year_id, term_number, name, start_date, end_date, next_term_begins, total_school_days)
                 VALUES (?,?,?,?,?,?,?)",
                [
                    $data['year_id'], $data['term_num'], $data['name'],
                    $data['start_date'], $data['end_date'], $data['next_begins'],
                    $data['days']
                ]
            );
            Session::flash('success', "Term '{$data['name']}' created.");
        }
        $this->back();
    }

    private function termDelete(): void {
        $id = (int)($_POST['term_id'] ?? 0);
        if (!$id) $this->back();
        $row = DB::queryOne("SELECT name FROM terms WHERE id = ?", [$id]);
        DB::execute("DELETE FROM terms WHERE id = ?", [$id]);
        Session::flash('success', "Term '{$row['name']}' deleted.");
        $this->back();
    }

    private function termActivate(): void {
        $id = (int)($_POST['term_id'] ?? 0);
        if (!$id) $this->back();
        // Deactivate all, then activate selected
        DB::execute("UPDATE terms SET is_active = 0");
        DB::execute("UPDATE terms SET is_active = 1 WHERE id = ?", [$id]);
        $row = DB::queryOne("SELECT name FROM terms WHERE id = ?", [$id]);
        Session::flash('success', "'{$row['name']}' is now the active term.");
        Session::updateActiveTerm();
        Notification::send(null, "Term Activated", "'{$row['name']}' has been set as the active term.", 'success', '/admin/terms');
        $this->back();
    }

    private function back(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/admin/years');
        exit;
    }
}
