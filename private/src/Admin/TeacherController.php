<?php
/**
 * Teacher Controller
 * Handles CRUD for teachers and their class/subject assignments
 */

class TeacherController {

    public function handle(): void {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid request token.');
                $this->redirect();
            }
            $action = $_POST['_action'] ?? '';
            match ($action) {
                'teacher_store'  => $this->teacherStore(),
                'teacher_delete' => $this->teacherDelete(),
                'teacher_toggle' => $this->teacherToggle(),
                'assign_subject' => $this->assignSubject(),
                'remove_subject' => $this->removeSubject(),
                default => $this->redirect(),
            };
        }

        // Prepare data for view
        global $teachersList, $classesList, $subjectsList, $assignmentsList;
        
        $teachersList = DB::query(
            "SELECT u.*, 
                    (SELECT COUNT(*) FROM classes c WHERE c.class_teacher_id = u.id) as class_count,
                    (SELECT COUNT(*) FROM class_subjects cs WHERE cs.teacher_id = u.id) as subject_count
             FROM users u 
             WHERE u.role = 'teacher' 
             ORDER BY u.full_name"
        );

        $classesList = DB::query(
            "SELECT c.id, c.class_name, c.section, sl.name as level_name
             FROM classes c
             JOIN school_levels sl ON sl.id = c.level_id
             JOIN academic_years ay ON ay.id = c.academic_year_id
             WHERE ay.is_active = 1
             ORDER BY sl.sort_order, c.class_name"
        );

        $subjectsList = DB::query(
            "SELECT s.id, s.subject_name, sl.name as level_name
             FROM subjects s
             JOIN school_levels sl ON sl.id = s.level_id
             WHERE s.is_active = 1
             ORDER BY sl.sort_order, s.subject_name"
        );

        $assignmentsList = DB::query(
            "SELECT cs.id, cs.teacher_id, c.class_name, c.section, s.subject_name 
             FROM class_subjects cs
             JOIN classes c ON c.id = cs.class_id
             JOIN subjects s ON s.id = cs.subject_id
             JOIN academic_years ay ON ay.id = c.academic_year_id
             WHERE ay.is_active = 1
             ORDER BY c.class_name, s.subject_name"
        );
    }

    private function teacherStore(): void {
        $rules = [
            'full_name' => 'required|max:200',
            'email'     => 'required|email|max:150',
        ];

        $v = Validator::make($_POST, $rules);
        if (!empty($v->errors())) {
            Session::flash('error', implode(' ', $v->allErrors()));
            $this->redirect();
        }

        $email = strtolower(trim($_POST['email']));
        $data = [
            'full_name' => trim($_POST['full_name']),
            'email'     => $email,
            'phone'     => trim($_POST['phone'] ?? ''),
            'role'      => 'teacher',
        ];

        $id = $_POST['teacher_id'] ?? null;
        if ($id) {
            DB::execute(
                "UPDATE users SET full_name=?, email=?, phone=? WHERE id=? AND role='teacher'",
                array_merge(array_values($data), [(int)$id])
            );
            Session::flash('success', "Teacher record updated.");
        } else {
            // Check for existing email
            $exists = DB::queryOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($exists) {
                Session::flash('error', "A user with this email already exists.");
                $this->redirect();
            }
            
            $data['password_hash'] = password_hash('password123', PASSWORD_BCRYPT); // Default password
            DB::insert(
                "INSERT INTO users (full_name, email, phone, role, password_hash) VALUES (?,?,?,?,?)",
                array_values($data)
            );
            Session::flash('success', "Teacher created with default password: password123");
        }
        $this->redirect();
    }

    private function teacherDelete(): void {
        $id = (int)($_POST['teacher_id'] ?? 0);
        
        // Prevent deleting if assigned to classes
        $hasClasses  = DB::queryOne("SELECT id FROM classes WHERE class_teacher_id = ? LIMIT 1", [$id]);
        $hasSubjects = DB::queryOne("SELECT id FROM class_subjects WHERE teacher_id = ? LIMIT 1", [$id]);
        
        if ($hasClasses || $hasSubjects) {
            Session::flash('error', "Cannot delete teacher. Please unassign them from all classes and subjects first.");
        } else {
            DB::execute("DELETE FROM users WHERE id = ? AND role = 'teacher'", [$id]);
            Session::flash('success', "Teacher removed.");
        }
        $this->redirect();
    }

    private function teacherToggle(): void {
        $id = (int)($_POST['teacher_id'] ?? 0);
        DB::execute("UPDATE users SET is_active = NOT is_active WHERE id = ? AND role = 'teacher'", [$id]);
        Session::flash('success', "Teacher status updated.");
        $this->redirect();
    }

    private function assignSubject(): void {
        $teacherId = (int)($_POST['teacher_id'] ?? 0);
        
        $classIds   = $_POST['class_ids'] ?? [];
        $subjectIds = $_POST['subject_ids'] ?? [];

        if (!is_array($classIds))   $classIds   = [(int)$classIds];
        if (!is_array($subjectIds)) $subjectIds = [(int)$subjectIds];

        $classIds   = array_filter(array_map('intval', $classIds));
        $subjectIds = array_filter(array_map('intval', $subjectIds));

        // Note: term_id is strictly required per term
        $activeYear = DB::queryOne("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
        $activeTerm = null;
        if ($activeYear) {
            $activeTerm = DB::queryOne("SELECT id FROM terms WHERE is_active = 1 AND academic_year_id = ? LIMIT 1", [$activeYear['id']]);
        }
        
        if (!$activeTerm) {
            Session::flash('error', "No active term found. Please activate an academic year and term first.");
            $this->redirect();
        }

        $termId = $activeTerm['id'];

        if ($teacherId && !empty($classIds) && !empty($subjectIds)) {
            $successCount = 0;
            $dupCount = 0;

            foreach ($classIds as $cid) {
                foreach ($subjectIds as $sid) {
                    try {
                        DB::insert(
                            "INSERT INTO class_subjects (class_id, subject_id, teacher_id, term_id) VALUES (?, ?, ?, ?)",
                            [$cid, $sid, $teacherId, $termId]
                        );
                        $successCount++;
                    } catch (\PDOException $e) {
                        if ($e->getCode() === '23000') {
                            // Update teacher if already exists for this class/subject/term
                            DB::execute(
                                "UPDATE class_subjects SET teacher_id = ? WHERE class_id = ? AND subject_id = ? AND term_id = ?",
                                [$teacherId, $cid, $sid, $termId]
                            );
                            $successCount++;
                        } else {
                            Session::flash('error', "Database error occurred during assignment.");
                            $this->redirect();
                        }
                    }
                }
            }
            
            if ($successCount > 0) {
                Session::flash('success', "Assigned {$successCount} subject/class combinations successfully.");
            }
        } else {
            Session::flash('error', "Please select at least one class and one subject.");
        }
        $this->redirect();
    }

    private function removeSubject(): void {
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        if ($assignmentId) {
            DB::execute("DELETE FROM class_subjects WHERE id = ?", [$assignmentId]);
            Session::flash('success', "Subject assignment removed.");
        }
        $this->redirect();
    }

    private function redirect(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/admin/teachers');
        exit;
    }
}
