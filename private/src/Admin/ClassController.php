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
        $classesList = [];
        if ($filterYear) {
            $classesList = DB::query(
                "SELECT c.*, sl.name as level_name, sl.code as level_code,
                        (SELECT GROUP_CONCAT(u.full_name SEPARATOR ', ')
                         FROM class_teachers ct JOIN users u ON u.id = ct.teacher_id
                         WHERE ct.class_id = c.id) as teacher_name,
                        (SELECT GROUP_CONCAT(ct.teacher_id SEPARATOR ',')
                         FROM class_teachers ct WHERE ct.class_id = c.id) as class_teacher_ids,
                        (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'active') as student_count,
                        (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'active' AND s.gender = 'Male') as male_count,
                        (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'active' AND s.gender = 'Female') as female_count
                 FROM classes c
                 JOIN school_levels sl ON sl.id = c.level_id
                 WHERE c.academic_year_id = ?
                 ORDER BY sl.sort_order, c.class_name",
                [$filterYear]
            );
        }
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
            'academic_year_id' => (int)$_POST['academic_year_id'],
            'grading_system'    => $_POST['grading_system'] ?? 'proficiency',
        ];

        $id = $_POST['class_id'] ?? null;
        try {
            if ($id) {
                DB::execute(
                    "UPDATE classes SET level_id=?, class_name=?, section=?, academic_year_id=?, grading_system=? WHERE id=?",
                    array_merge(array_values($data), [(int)$id])
                );
                $this->syncClassTeachers((int)$id, $_POST['teacher_ids'] ?? []);
                Session::flash('success', "Classroom '{$data['class_name']}' updated.");
            } else {
                $newId = DB::insert(
                    "INSERT INTO classes (level_id, class_name, section, academic_year_id, grading_system) VALUES (?,?,?,?,?)",
                    array_values($data)
                );
                $this->syncClassTeachers((int)$newId, $_POST['teacher_ids'] ?? []);
                Session::flash('success', "Classroom '{$data['class_name']}' created.");
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation
                Session::flash('error', "A classroom with this name and section already exists for the selected academic year.");
            } else {
                Session::flash('error', "A database error occurred while saving the classroom.");
            }
        }
        $this->back();
    }

    private function syncClassTeachers(int $classId, array $teacherIds): void {
        $className = DB::queryValue("SELECT class_name FROM classes WHERE id = ?", [$classId]);
        
        // Get existing assigned teachers to identify new ones
        $existing = DB::query("SELECT teacher_id FROM class_teachers WHERE class_id = ?", [$classId]);
        $existingIds = array_column($existing, 'teacher_id');

        DB::execute("DELETE FROM class_teachers WHERE class_id = ?", [$classId]);
        foreach ($teacherIds as $tid) {
            $tid = (int)$tid;
            if ($tid && $classId) {
                DB::execute("INSERT IGNORE INTO class_teachers (class_id, teacher_id) VALUES (?, ?)", [$classId, $tid]);
                
                // Notify ONLY if they weren't already assigned (HCI: reduce spam)
                if (!in_array($tid, $existingIds)) {
                    require_once __DIR__ . '/../Helpers/Notification.php';
                    Notification::send(
                        $tid, 
                        "New Classroom Assignment", 
                        "You have been assigned as a Class Teacher for '{$className}'.", 
                        "success", 
                        "/teacher/class?id={$classId}"
                    );
                }
            }
        }
    }

    private function classDelete(): void {
        $id  = (int)($_POST['class_id'] ?? 0);
        $row = DB::queryOne("SELECT class_name FROM classes WHERE id = ?", [$id]);
        
        if (!$row) {
            Session::flash('error', 'Classroom not found.');
            $this->back();
        }

        // 1. Check for ANY students tied to this class (active/inactive/transferred)
        // Foreign key students_ibfk_1 would block this if ANY student exists.
        $studentCount = (int)DB::queryValue("SELECT COUNT(*) FROM students WHERE current_class_id = ?", [$id]);
        if ($studentCount > 0) {
            Session::flash('error', "Cannot delete '{$row['class_name']}' because it has {$studentCount} associated student records. Please move students to another class or academic year first.");
            $this->back();
        }

        // 2. Check for academic records or published reports
        $hasScores = (int)DB::queryValue("SELECT COUNT(*) FROM computed_scores cs JOIN class_subjects csb ON csb.id = cs.class_subject_id WHERE csb.class_id = ?", [$id]);
        $hasAggs   = (int)DB::queryValue("SELECT COUNT(*) FROM student_aggregates WHERE class_id = ?", [$id]);
        $isPublished = (int)DB::queryValue("SELECT COUNT(*) FROM report_card_locks WHERE class_id = ?", [$id]);
        
        if ($hasScores > 0 || $hasAggs > 0 || $isPublished > 0) {
            Session::flash('error', "Cannot delete '{$row['class_name']}' because it contains academic results, computed ranks, or published report statuses. Deleting it would break historical data.");
            $this->back();
        }

        // 3. Cleanup dependencies (stuff that doesn't have CASCADE or is safe to remove if the class is "empty")
        try {
            DB::beginTransaction();
            
            // Delete teacher assignments
            DB::execute("DELETE FROM class_teachers WHERE class_id = ?", [$id]);
            
            // Delete subject assignments (only if they have no scores, which we checked above)
            DB::execute("DELETE FROM class_subjects WHERE class_id = ?", [$id]);
            
            // Delete the class itself
            DB::execute("DELETE FROM classes WHERE id = ?", [$id]);
            
            DB::commit();
            Session::flash('success', "Classroom '{$row['class_name']}' deleted successfully.");
        } catch (Exception $e) {
            DB::rollBack();
            Session::flash('error', "Failed to delete classroom. Database error: " . $e->getMessage());
        }

        $this->back();
    }

    private function back(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/admin/classes');
        exit;
    }
}
