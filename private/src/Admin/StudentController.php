<?php
/**
 * Student Controller
 * Handles student registration, profile management, and class rosters
 */

class StudentController {

    public function handle(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_REQUEST['_action'] ?? '';

        // Handle Standalone Actions (GET or POST)
        if ($action === 'parent_get') {
            $this->parentGet();
        }
        if ($action === 'student_bulk_tpl') {
            $this->studentBulkTemplate();
        }

        if ($method === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Your session expired due to inactivity. For your security, please try your action again.');
                $this->redirect();
            }
            match ($action) {
                'student_store'       => $this->studentStore(),
                'student_delete'      => $this->studentDelete(),
                'student_status'      => $this->studentStatus(),
                'parent_link'         => $this->parentLink(),
                'parent_unlink'       => $this->parentUnlink(),
                'student_bulk_import' => $this->studentBulkImport(),
                default               => $this->redirect(),
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

        $filterYear  = !empty($_GET['year_id']) ? $_GET['year_id'] : $activeYearId;
        $filterClass = !empty($_GET['class_id']) ? $_GET['class_id'] : null;

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
                     WHERE sp2.student_id = s.id) as linked_parents,
                    (EXISTS(SELECT 1 FROM sba_component_scores WHERE student_id = s.id) OR
                     EXISTS(SELECT 1 FROM exam_scores WHERE student_id = s.id) OR
                     EXISTS(SELECT 1 FROM attendance WHERE student_id = s.id) OR
                     EXISTS(SELECT 1 FROM student_remarks WHERE student_id = s.id)) as has_records
             FROM students s
             LEFT JOIN classes c ON c.id = s.current_class_id
             $where
             ORDER BY s.gender ASC, s.full_name ASC",
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
            'status'            => $_POST['status'] ?? 'active',
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
                "UPDATE students SET student_id_number=?, full_name=?, surname=?, gender=?, date_of_birth=?, current_class_id=?, academic_year_id=?, status=? WHERE id=?",
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
                "INSERT INTO students (student_id_number, full_name, surname, gender, date_of_birth, current_class_id, academic_year_id, status) VALUES (?,?,?,?,?,?,?,?)",
                array_values($data)
            );
            Session::flash('success', "Student '{$data['full_name']}' registered successfully.");
        }
        $this->redirect();
    }

    private function studentDelete(): void {
        $id = (int)($_POST['student_id'] ?? 0);
        
        // Safety check: Don't delete if they have any recorded academic/attendance data
        // Check multiple tables where student data might exist
        $hasRecords = DB::queryOne("
            SELECT id FROM sba_component_scores WHERE student_id = ?
            UNION ALL
            SELECT id FROM exam_scores WHERE student_id = ?
            UNION ALL
            SELECT id FROM attendance WHERE student_id = ?
            UNION ALL
            SELECT id FROM student_remarks WHERE student_id = ?
            UNION ALL
            SELECT id FROM computed_scores WHERE student_id = ?
            UNION ALL
            SELECT id FROM student_aggregates WHERE student_id = ?
            UNION ALL
            SELECT id FROM student_promotions WHERE student_id = ?
            LIMIT 1
        ", [$id, $id, $id, $id, $id, $id, $id]);

        if ($hasRecords) {
            Session::flash('error', "Cannot delete student with recorded scores. Please set status to 'inactive' instead.");
        } else {
            // Also clean up parent links if deleting (though ON DELETE CASCADE should handle it, we're explicit here)
            DB::execute("DELETE FROM student_parents WHERE student_id = ?", [$id]);
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
        $parentName   = trim($_POST['parent_name'] ?? 'Parent/Guardian');

        if (!$studentId || !$phone) {
            Session::flash('error', 'Student and phone number are required.');
            $this->redirect();
        }

        // Enforce "One Active Phone" rule per student
        DB::execute("DELETE FROM student_parents WHERE student_id = ?", [$studentId]);

        // Look up parent user by phone; create if not exists
        $user = DB::queryOne(
            "SELECT id, full_name FROM users WHERE phone = ? AND role = 'parent'",
            [$phone]
        );

        if (!$user) {
            $parentUserId = DB::insert(
                "INSERT INTO users (full_name, phone, role, is_active) VALUES (?, ?, 'parent', 1)",
                [$parentName ?: 'Parent/Guardian', $phone]
            );
        } else {
            $parentUserId = $user['id'];
            // Also update the parent's name in the users table for consistency
            DB::execute("UPDATE users SET full_name = ? WHERE id = ?", [$parentName ?: 'Parent/Guardian', $parentUserId]);
        }

        DB::insert(
            "INSERT INTO student_parents (student_id, parent_user_id, relationship, is_primary) VALUES (?, ?, ?, 1)",
            [$studentId, $parentUserId, $relationship]
        );

        Session::flash('success', 'Parent/Guardian contact updated successfully.');
        $this->redirect();
    }

    private function parentGet(): void {
        $studentId = (int)($_GET['student_id'] ?? 0);
        $parents = DB::query("
            SELECT sp.id as link_id, sp.relationship, u.full_name as parent_name, u.phone as parent_phone
            FROM student_parents sp
            JOIN users u ON u.id = sp.parent_user_id
            WHERE sp.student_id = ?
        ", [$studentId]);
        header('Content-Type: application/json');
        echo json_encode($parents);
        exit;
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
        $yearId  = $_GET['year_id'] ?? $_POST['academic_year_id'] ?? '';
        $classId = $_GET['class_id'] ?? $_POST['current_class_id'] ?? '';
        
        $query = [];
        if ($yearId)  $query[] = "year_id=" . urlencode((string)$yearId);
        if ($classId) $query[] = "class_id=" . urlencode((string)$classId);
        
        $qs = !empty($query) ? '?' . implode('&', $query) : '';
        
        header("Location: $base/admin/students" . $qs);
        exit;
    }

    // ── Bulk CSV Import ───────────────────────────────────────────────

    /**
     * Download a blank CSV template for bulk student import.
     */
    private function studentBulkTemplate(): void {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="student_import_template.csv"');
        header('Cache-Control: no-cache');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Full_Name', 'Class', 'Section', 'Gender']);
        fputcsv($out, ['ADJEI VICENT', 'BASIC 1', '', 'Male']);
        fputcsv($out, ['ADOMAKO CECILIA', 'BASIC 4', '', 'Female']);
        fputcsv($out, ['OSEI KOFI', 'BASIC 5', 'A', 'Male']);
        fclose($out);
        exit;
    }

    /**
     * Bulk import students from a CSV upload.
     * CSV columns: Full_Name, Class, Section, Gender
     * - Auto-generates Student ID numbers (zero-padded 4-digit)
     * - Skips rows where same full_name already exists in that class
     * - Resolves class by class_name + section against DB
     */
    private function studentBulkImport(): void {
        $file = $_FILES['import_csv'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::set('bulk_import_result', ['error' => 'No file uploaded or upload failed.']);
            $this->redirect();
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            Session::set('bulk_import_result', ['error' => 'Only .csv files are accepted. Open your Excel file and use File → Save As → CSV.']);
            $this->redirect();
        }

        $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
        if (!$academicYearId) {
            Session::set('bulk_import_result', ['error' => 'Please select an Academic Year before importing.']);
            $this->redirect();
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Session::set('bulk_import_result', ['error' => 'Could not read the uploaded file.']);
            $this->redirect();
        }

        // Build class lookup: "BASIC 1|" => id, "BASIC 5|A" => id, etc.
        $allClasses = DB::query("SELECT id, class_name, section FROM classes");
        $classMap   = [];
        foreach ($allClasses as $cls) {
            $key = strtoupper(trim($cls['class_name'])) . '|' . strtoupper(trim($cls['section']));
            $classMap[$key] = (int)$cls['id'];
        }

        // Get next available student ID number
        $maxId = DB::queryValue("SELECT MAX(CAST(student_id_number AS UNSIGNED)) FROM students") ?? 0;
        $nextId = (int)$maxId + 1;

        // Read and skip header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            Session::set('bulk_import_result', ['error' => 'CSV file appears to be empty.']);
            $this->redirect();
        }

        // Normalize header
        $header = array_map(fn($h) => strtolower(trim($h)), $header);
        $nameIdx    = array_search('full_name', $header);
        $classIdx   = array_search('class', $header);
        $sectionIdx = array_search('section', $header);
        $genderIdx  = array_search('gender', $header);

        if ($nameIdx === false || $classIdx === false) {
            fclose($handle);
            Session::set('bulk_import_result', [
                'error' => 'Invalid CSV format. Required columns: Full_Name, Class (and optionally Section, Gender). Download the template for guidance.'
            ]);
            $this->redirect();
        }

        $inserted  = 0;
        $skipped   = 0;
        $rowErrors = [];
        $lineNum   = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNum++;
            $fullName = strtoupper(trim($row[$nameIdx] ?? ''));
            if ($fullName === '') {
                $skipped++;
                continue; // blank row
            }

            $className   = strtoupper(trim($row[$classIdx] ?? ''));
            $sectionName = strtoupper(trim($row[$sectionIdx] ?? ''));
            $gender      = ucfirst(strtolower(trim($row[$genderIdx] ?? '')));
            if (!in_array($gender, ['Male', 'Female', 'Other'])) $gender = null;

            $classKey = $className . '|' . $sectionName;
            if (!isset($classMap[$classKey])) {
                $rowErrors[] = "Row {$lineNum}: Class \u00ab{$className}" . ($sectionName ? " ({$sectionName})" : '') . "\u00bb not found in the system.";
                $skipped++;
                continue;
            }
            $classId = $classMap[$classKey];

            // Duplicate check: same name in same class
            $exists = DB::queryOne(
                "SELECT id FROM students WHERE UPPER(full_name) = ? AND current_class_id = ?",
                [$fullName, $classId]
            );
            if ($exists) {
                $skipped++;
                continue;
            }

            $studentIdNum = str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
            DB::execute(
                "INSERT INTO students (student_id_number, full_name, gender, current_class_id, academic_year_id, status) VALUES (?,?,?,?,?,'active')",
                [$studentIdNum, $fullName, $gender ?: null, $classId, $academicYearId]
            );
            $nextId++;
            $inserted++;
        }
        fclose($handle);

        Session::set('bulk_import_result', [
            'inserted'  => $inserted,
            'skipped'   => $skipped,
            'rowErrors' => $rowErrors,
        ]);
        $this->redirect();
    }
}
