<?php
/**
 * Student Controller
 * Handles student registration, profile management, and class rosters
 */

class StudentController {

    public function handle(): void {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid request token.');
                $this->redirect();
            }
            $action = $_POST['_action'] ?? '';
            match ($action) {
                'student_store'    => $this->studentStore(),
                'student_delete'   => $this->studentDelete(),
                'student_status'   => $this->studentStatus(),
                'parent_link'      => $this->parentLink(),
                'parent_unlink'    => $this->parentUnlink(),
                default => $this->redirect(),
            };
        }

        // Prepare data for view
        global $studentsList, $classesList, $activeYearId, $yearsList;

        $activeYear   = DB::queryOne("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
        if (!$activeYear) {
            $activeYear = DB::queryOne("SELECT id FROM academic_years ORDER BY year_name DESC LIMIT 1");
        }
        $activeYearId = $activeYear['id'] ?? null;
        $yearsList    = DB::query("SELECT id, year_name FROM academic_years ORDER BY year_name DESC");

        $filterYear  = $_GET['year_id']  ?? $activeYearId;
        $filterClass = $_GET['class_id'] ?? null;

        $classesList = DB::query(
            "SELECT c.id, c.class_name, c.section, sl.name as level_name
             FROM classes c
             JOIN school_levels sl ON sl.id = c.level_id
             WHERE c.academic_year_id = ?
             ORDER BY sl.sort_order, c.class_name",
            [$filterYear]
        );

        $params = [$filterYear];
        $where  = "WHERE s.academic_year_id = ?";
        
        if ($filterClass) {
            $where .= " AND s.current_class_id = ?";
            $params[] = $filterClass;
        }

        $studentsList = DB::query(
            "SELECT s.*, c.class_name, c.section,
                    (SELECT GROUP_CONCAT(u.full_name, ' (', u.phone, ')' SEPARATOR ', ')
                     FROM student_parents sp2
                     JOIN users u ON u.id = sp2.parent_user_id
                     WHERE sp2.student_id = s.id) as linked_parents
             FROM students s
             LEFT JOIN classes c ON c.id = s.current_class_id
             $where
             ORDER BY s.full_name",
            $params
        );
    }

    private function studentStore(): void {
        $rules = [
            'full_name'         => 'required|max:200',
            'student_id_number' => 'required|max:50',
            'current_class_id'  => 'required|integer',
            'academic_year_id'  => 'required|integer',
        ];

        $v = Validator::make($_POST, $rules);
        if (!empty($v->errors())) {
            Session::flash('error', implode(' ', $v->allErrors()));
            $this->redirect();
        }

        $data = [
            'student_id_number' => trim($_POST['student_id_number']),
            'full_name'         => trim($_POST['full_name']),
            'surname'           => trim($_POST['surname'] ?? ''),
            'gender'            => $_POST['gender'] ?? null,
            'date_of_birth'     => $_POST['date_of_birth'] ?: null,
            'current_class_id'  => (int)$_POST['current_class_id'],
            'academic_year_id'  => (int)$_POST['academic_year_id'],
        ];

        $id = $_POST['student_id'] ?? null;
        if ($id) {
            // Check if another student already uses this ID number
            $conflict = DB::queryOne(
                "SELECT id FROM students WHERE student_id_number = ? AND id != ? LIMIT 1",
                [$data['student_id_number'], (int)$id]
            );
            if ($conflict) {
                Session::flash('error', "Student ID '{$data['student_id_number']}' is already in use by another student.");
                $this->redirect();
            }
            DB::execute(
                "UPDATE students SET student_id_number=?, full_name=?, surname=?, gender=?, date_of_birth=?, current_class_id=?, academic_year_id=? WHERE id=?",
                array_merge(array_values($data), [(int)$id])
            );
            Session::flash('success', "Student record updated.");
        } else {
            // Check for duplicate student ID
            $exists = DB::queryOne("SELECT id FROM students WHERE student_id_number = ? LIMIT 1", [$data['student_id_number']]);
            if ($exists) {
                Session::flash('error', "Student ID '{$data['student_id_number']}' is already in use.");
                $this->redirect();
            }
            
            DB::insert(
                "INSERT INTO students (student_id_number, full_name, surname, gender, date_of_birth, current_class_id, academic_year_id) VALUES (?,?,?,?,?,?,?)",
                array_values($data)
            );
            Session::flash('success', "Student '{$data['full_name']}' registered successfully.");
        }
        $this->redirect();
    }

    private function studentDelete(): void {
        $id = (int)($_POST['student_id'] ?? 0);
        
        // Safety check: Don't delete if they have scores recorded (usually better to 'inactive' them)
        $hasScores = DB::queryOne("SELECT id FROM sba_component_scores WHERE student_id = ? LIMIT 1", [$id]);
        if ($hasScores) {
            Session::flash('error', "Cannot delete student with recorded scores. Please set status to 'inactive' instead.");
        } else {
            DB::execute("DELETE FROM students WHERE id = ?", [$id]);
            Session::flash('success', "Student record removed.");
        }
        $this->redirect();
    }

    private function studentStatus(): void {
        $id     = (int)($_POST['student_id'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        DB::execute("UPDATE students SET status = ? WHERE id = ?", [$status, $id]);
        Session::flash('success', "Student status updated to $status.");
        $this->redirect();
    }

    // ── Parent Linking ────────────────────────────────────────
    private function parentLink(): void {
        $studentId    = (int)($_POST['student_id'] ?? 0);
        $phone        = trim($_POST['parent_phone'] ?? '');
        $relationship = trim($_POST['relationship'] ?? 'Parent/Guardian');

        if (!$studentId || !$phone) {
            Session::flash('error', 'Student and phone number are required.');
            $this->redirect();
        }

        // Look up parent user by phone; create if not exists
        $user = DB::queryOne(
            "SELECT id, full_name FROM users WHERE phone = ? AND role = 'parent'",
            [$phone]
        );

        if (!$user) {
            // Auto-create a parent account (name can be updated later)
            $parentName  = trim($_POST['parent_name'] ?? 'Parent/Guardian');
            $parentName  = $parentName ?: 'Parent/Guardian';
            $newId = DB::insert(
                "INSERT INTO users (full_name, phone, role, is_active) VALUES (?, ?, 'parent', 1)",
                [$parentName, $phone]
            );
            $parentUserId = $newId;
        } else {
            $parentUserId = $user['id'];
        }

        // Check not already linked
        $existing = DB::queryOne(
            "SELECT id FROM student_parents WHERE student_id = ? AND parent_user_id = ?",
            [$studentId, $parentUserId]
        );

        if ($existing) {
            Session::flash('error', 'This parent is already linked to this student.');
            $this->redirect();
        }

        DB::insert(
            "INSERT INTO student_parents (student_id, parent_user_id, relationship) VALUES (?, ?, ?)",
            [$studentId, $parentUserId, $relationship]
        );

        Session::flash('success', 'Parent/Guardian linked successfully. They can now log in using their phone number.');
        $this->redirect();
    }

    private function parentUnlink(): void {
        $linkId = (int)($_POST['link_id'] ?? 0);
        if (!$linkId) {
            Session::flash('error', 'Invalid link.');
            $this->redirect();
        }
        DB::execute("DELETE FROM student_parents WHERE id = ?", [$linkId]);
        Session::flash('success', 'Parent/Guardian unlinked.');
        $this->redirect();
    }

    private function redirect(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        $classId = $_GET['class_id'] ?? '';
        $yearId  = $_GET['year_id'] ?? '';
        header("Location: $base/admin/students?year_id=$yearId&class_id=$classId");
        exit;
    }
}
