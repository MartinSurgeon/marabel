<?php
/**
 * Student Import Controller
 * Handles bulk CSV uploads for student registrations
 */

class StudentImportController {

    public function handle(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify()) {
                Session::set('bulk_import_result', ['error' => 'Invalid or expired security token. Please try again.']);
                $this->redirect();
            }

            $action = $_GET['action'] ?? '';
            
            if ($action === 'template') {
                $this->downloadTemplate();
            } elseif ($action === 'upload') {
                $this->processUpload();
            } else {
                $this->redirect();
            }
        }

        // For GET requests, load data for the view
        global $yearsList, $activeYearId;

        $activeYear   = DB::queryOne("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
        if (!$activeYear) {
            $activeYear = DB::queryOne("SELECT id FROM academic_years ORDER BY year_name DESC LIMIT 1");
        }
        $activeYearId = $activeYear['id'] ?? null;
        $yearsList    = DB::query("SELECT id, year_name FROM academic_years ORDER BY year_name DESC");
    }

    private function downloadTemplate(): void {
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

    private function processUpload(): void {
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
                'error' => 'Invalid CSV format. Required columns: Full_Name, Class. Optional: Section, Gender.'
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
            $sectionName = $sectionIdx !== false ? strtoupper(trim($row[$sectionIdx] ?? '')) : '';
            $genderRaw   = $genderIdx !== false ? trim($row[$genderIdx] ?? '') : '';
            $gender      = ucfirst(strtolower($genderRaw));
            if (!in_array($gender, ['Male', 'Female', 'Other'])) $gender = null;

            $classKey = $className . '|' . $sectionName;
            if (!isset($classMap[$classKey])) {
                $rowErrors[] = "Row {$lineNum}: Class «{$className}" . ($sectionName ? " ({$sectionName})" : '') . "» not found in the system.";
                $skipped++;
                continue;
            }
            $classId = $classMap[$classKey];

            // Duplicate check
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

    private function redirect(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header("Location: $base/admin/import");
        exit;
    }
}
