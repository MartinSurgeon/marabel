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
                'bulk_remove_subjects' => $this->bulkRemoveSubjects(),
                default => $this->redirect(),
            };
        }

        // Prepare data for view
        global $teachersList, $classesList, $subjectsList, $assignmentsList;
        
        $activeYear = DB::queryOne("SELECT id, year_name FROM academic_years WHERE is_active = 1 LIMIT 1");
        $activeYearId = $activeYear['id'] ?? null;
        $activeYearName = $activeYear['year_name'] ?? 'None';

        $teachersList = DB::query(
            "SELECT u.*, 
                    (SELECT COUNT(*) FROM class_teachers ct WHERE ct.teacher_id = u.id) as class_count,
                    (SELECT COUNT(*) FROM class_subjects cs WHERE cs.teacher_id = u.id) as subject_count,
                    (SELECT COUNT(*) FROM class_subjects cs 
                     JOIN classes c ON c.id = cs.class_id
                     WHERE cs.teacher_id = u.id AND c.academic_year_id = ?) as current_year_subjects,
                    (SELECT GROUP_CONCAT(DISTINCT CONCAT(c.class_name, ' (', s.subject_name, ')') SEPARATOR ', ')
                     FROM class_subjects cs 
                     JOIN classes c ON c.id = cs.class_id
                     JOIN subjects s ON s.id = cs.subject_id
                     WHERE cs.teacher_id = u.id AND c.academic_year_id = ?) as assignment_summary,
                    (SELECT GROUP_CONCAT(DISTINCT CONCAT(c.class_name, IFNULL(c.section, '')) SEPARATOR ', ')
                     FROM class_teachers ct 
                     JOIN classes c ON c.id = ct.class_id
                     WHERE ct.teacher_id = u.id) as lead_classes,
                    (SELECT GROUP_CONCAT(DISTINCT class_id)
                     FROM class_teachers ct WHERE ct.teacher_id = u.id) as assigned_class_ids
             FROM users u 
             WHERE u.role = 'teacher' 
             ORDER BY u.full_name",
            [$activeYearId, $activeYearId]
        );

        global $activeYearName; // Make it available for template

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
                [$data['full_name'], $data['email'], $data['phone'], (int)$id]
            );
            $this->syncTeacherClassrooms((int)$id, $_POST['class_ids'] ?? []);
            Session::flash('success', "Teacher record updated.");
        } else {
            // Check for existing email
            $exists = DB::queryOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($exists) {
                Session::flash('error', "A user with this email already exists.");
                $this->redirect();
            }
            
            $data['password_hash'] = password_hash('password123', PASSWORD_BCRYPT); // Default password
            $newId = DB::insert(
                "INSERT INTO users (full_name, email, phone, role, password_hash) VALUES (?,?,?,?,?)",
                array_values($data)
            );
            $this->syncTeacherClassrooms((int)$newId, $_POST['class_ids'] ?? []);
            
            // Notifications
            require_once __DIR__ . '/../Helpers/Notification.php';
            // 1. Welcome the new teacher
            Notification::send(
                (int)$newId,
                "Welcome to " . SCHOOL_NAME,
                "Your account has been created. Your default password is: password123",
                "success"
            );
            // 2. Alert admins (Audit)
            Notification::sendToRole(
                'admin',
                "New Teacher Joined",
                "Staff member '{$data['full_name']}' was registered by " . Session::get('user_name') . ".",
                "info"
            );

            Session::flash('success', "Teacher created with default password: password123");
        }
        $this->redirect();
    }

    private function syncTeacherClassrooms(int $teacherId, array $classIds): void {
        DB::execute("DELETE FROM class_teachers WHERE teacher_id = ?", [$teacherId]);
        foreach ($classIds as $classId) {
            $classId = (int)$classId;
            if ($classId && $teacherId) {
                DB::execute("INSERT IGNORE INTO class_teachers (class_id, teacher_id) VALUES (?, ?)", [$classId, $teacherId]);
            }
        }
    }

    private function teacherDelete(): void {
        $id = (int)($_POST['teacher_id'] ?? 0);
        
        // Prevent deleting if assigned to classes
        $hasClasses  = DB::queryOne("SELECT class_id FROM class_teachers WHERE teacher_id = ? LIMIT 1", [$id]);
        $hasSubjects = DB::queryOne("SELECT id FROM class_subjects WHERE teacher_id = ? LIMIT 1", [$id]);
        
        if ($hasClasses || $hasSubjects) {
            Session::flash('error', "Cannot delete teacher. Please unassign them from all classrooms and subjects first.");
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
                // Notify Teacher
                require_once __DIR__ . '/../Helpers/Notification.php';
                $classNames = [];
                $q = "SELECT class_name FROM classes WHERE id IN (" . implode(',', array_fill(0, count($classIds), '?')) . ")";
                $cData = DB::query($q, $classIds);
                $classNames = array_column($cData, 'class_name');
                
                $msg = "You have been assigned new subject(s) in: " . implode(', ', $classNames) . ".";
                Notification::send($teacherId, "New Subject Assignment", $msg, "success", "/teacher/scores");
                
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
            // Check for scores across all relevant tables
            $hasSba  = (int)DB::queryValue("SELECT COUNT(*) FROM sba_component_scores WHERE class_subject_id = ?", [$assignmentId]);
            $hasEx   = (int)DB::queryValue("SELECT COUNT(*) FROM exam_scores WHERE class_subject_id = ?", [$assignmentId]);
            $hasComp = (int)DB::queryValue("SELECT COUNT(*) FROM computed_scores WHERE class_subject_id = ?", [$assignmentId]);

            if ($hasSba > 0 || $hasEx > 0 || $hasComp > 0) {
                // If scores exist, we cannot delete the record due to FK constraints.
                // Instead, we NULL out the teacher_id to unassign the teacher.
                DB::execute("UPDATE class_subjects SET teacher_id = NULL WHERE id = ?", [$assignmentId]);
                Session::flash('info', "Teacher unassigned. The subject record was preserved because it contains existing student scores.");
            } else {
                // If no scores exist, we can safely delete the assignment record entirely.
                DB::execute("DELETE FROM class_subjects WHERE id = ?", [$assignmentId]);
                Session::flash('success', "Subject assignment removed.");
            }
        }
        $this->redirect();
    }

    private function bulkRemoveSubjects(): void {
        $assignmentIds = $_POST['assignment_ids'] ?? [];
        if (!is_array($assignmentIds)) $assignmentIds = [$assignmentIds];
        $assignmentIds = array_filter(array_map('intval', $assignmentIds));

        if (!empty($assignmentIds)) {
            $removed = 0;
            $unassigned = 0;
            foreach ($assignmentIds as $id) {
                // Check for scores across all relevant tables (same logic as removeSubject)
                $hasSba  = (int)DB::queryValue("SELECT COUNT(*) FROM sba_component_scores WHERE class_subject_id = ?", [$id]);
                $hasEx   = (int)DB::queryValue("SELECT COUNT(*) FROM exam_scores WHERE class_subject_id = ?", [$id]);
                $hasComp = (int)DB::queryValue("SELECT COUNT(*) FROM computed_scores WHERE class_subject_id = ?", [$id]);

                if ($hasSba > 0 || $hasEx > 0 || $hasComp > 0) {
                    DB::execute("UPDATE class_subjects SET teacher_id = NULL WHERE id = ?", [$id]);
                    $unassigned++;
                } else {
                    DB::execute("DELETE FROM class_subjects WHERE id = ?", [$id]);
                    $removed++;
                }
            }
            Session::flash('success', "Processed " . ($removed + $unassigned) . " subjects. ($removed deleted, $unassigned unassigned due to existing scores)");
        }
        $this->redirect();
    }

    private function redirect(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/admin/teachers');
        exit;
    }
}
