<?php

class ExportController {

    public function handle(): void {
        Session::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid token. Please try again.');
                $this->redirect();
            }

            $action = $_POST['_action'] ?? '';
            if ($action === 'generate_export') {
                $this->generateExport();
                exit; // Stop execution to push download
            }
        }

        // GET Request Variables for the UI
        global $classes, $terms;
        $classes = DB::query("SELECT id, class_name, section FROM classes ORDER BY class_name ASC, section ASC");
        $terms = DB::query("SELECT t.id, t.name, ay.year_name, t.is_active 
                            FROM terms t 
                            JOIN academic_years ay ON ay.id = t.academic_year_id 
                            ORDER BY ay.year_name DESC, t.term_number DESC");
    }

    private function generateExport(): void {
        $type    = $_POST['export_type'] ?? 'students';
        $classId = $_POST['class_id']    ?? 'all';
        $termId  = $_POST['term_id']     ?? 0;
        $status  = $_POST['status']      ?? 'active';
        $format  = $_POST['export_format'] ?? 'csv';
        $columns = array_unique($_POST['columns'] ?? []);

        // Always include basic identifier columns for integrity even if UI disabled input
        if (!in_array('student_id_number', $columns) && in_array($type, ['results', 'attendance'])) {
            array_unshift($columns, 'student_id_number');
        }
        if (!in_array('full_name', $columns) && $type != 'staff') {
            array_unshift($columns, 'full_name');
        }

        switch ($type) {
            case 'students':
                $this->exportStudents($classId, $status, $columns, $format);
                break;
            case 'results':
                $this->exportResults($classId, (int)$termId, $status, $columns, $format);
                break;
            case 'staff':
                $this->exportStaff($status, $columns, $format);
                break;
            case 'attendance':
                $this->exportAttendance($classId, (int)$termId, $status, $columns, $format);
                break;
            case 'sms':
                $this->exportSmsLogs($columns, $format);
                break;
            default:
                Session::flash('error', 'Invalid export type selected.');
                $this->redirect();
        }
    }

    private function exportStudents($classId, $status, array $columns, string $format): void {
        $params = [];
        $where = ["1=1"];

        if ($classId !== 'all') {
            $where[] = "s.current_class_id = ?";
            $params[] = $classId;
        }

        if ($status === 'active') {
            $where[] = "s.status = 'active'";
        }

        $whereClause = implode(' AND ', $where);

        $query = "
            SELECT 
                s.student_id_number,
                s.full_name,
                s.gender,
                s.date_of_birth,
                s.status,
                c.class_name,
                (SELECT GROUP_CONCAT(u.full_name SEPARATOR ' / ')
                 FROM student_parents sp
                 JOIN users u ON u.id = sp.parent_user_id
                 WHERE sp.student_id = s.id) as parent_name,
                (SELECT GROUP_CONCAT(u.phone SEPARATOR ' / ')
                 FROM student_parents sp
                 JOIN users u ON u.id = sp.parent_user_id
                 WHERE sp.student_id = s.id) as parent_phone
            FROM students s
            LEFT JOIN classes c ON c.id = s.current_class_id
            WHERE {$whereClause}
            ORDER BY c.class_name ASC, s.gender ASC, s.full_name ASC
        ";

        $data = DB::query($query, $params);

        // Header mapping for simple English
        $colMap = [
            'student_id_number' => 'Student ID',
            'full_name' => 'Full Name',
            'gender' => 'Gender',
            'date_of_birth' => 'Date of Birth',
            'class_name' => 'Classroom',
            'status' => 'Status',
            'parent_name' => 'Parent Name',
            'parent_phone' => 'Parent Phone'
        ];

        if ($format === 'excel') {
            $this->outputExcel("students_export", $data, $columns, $colMap);
        } else {
            $this->outputCSV("students_export", $data, $columns, $colMap);
        }
    }

    private function exportResults($classId, int $termId, $status, array $columns, string $format): void {
        $params = [$termId];
        $where = ["sa.term_id = ?"];

        if ($classId !== 'all') {
            $where[] = "sa.class_id = ?";
            $params[] = $classId;
        }

        if ($status === 'active') {
            $where[] = "s.status = 'active'";
        }

        $whereClause = implode(' AND ', $where);

        $query = "
            SELECT 
                s.student_id_number,
                s.full_name,
                c.class_name,
                sa.aggregate_score,
                sa.class_position,
                sa.student_id,
                sa.class_id
            FROM student_aggregates sa
            JOIN students s ON s.id = sa.student_id
            JOIN classes c ON c.id = sa.class_id
            WHERE {$whereClause}
            ORDER BY c.class_name ASC, sa.class_position ASC
        ";

        $data = DB::query($query, $params);

        $includeDetails = in_array('subject_breakdown', $columns);
        if ($includeDetails) {
            // Remove 'subject_breakdown' from physical columns requested mapping
            $columns = array_diff($columns, ['subject_breakdown']);
            
            // Build subjects dynamically
            foreach ($data as &$row) {
                $subjects = DB::query("
                    SELECT subj.subject_name, cs.overall_total
                    FROM computed_scores cs
                    JOIN class_subjects c_subj ON c_subj.id = cs.class_subject_id
                    JOIN subjects subj ON subj.id = c_subj.subject_id
                    WHERE cs.student_id = ? AND cs.term_id = ?
                ", [$row['student_id'], $termId]);

                foreach ($subjects as $subj) {
                    $colName = $subj['subject_name'] . ' (Score)';
                    $row[$colName] = $subj['overall_total'];
                    if (!in_array($colName, $columns)) {
                        $columns[] = $colName; // Append dynamic subject headers
                    }
                }
            }
        }

        $colMap = [
            'student_id_number' => 'Student ID',
            'full_name' => 'Full Name',
            'class_name' => 'Classroom',
            'aggregate_score' => 'Aggregate Score',
            'class_position' => 'Class Position'
        ];
        
        // Pass map and append dynamic ones
        foreach($columns as $c) {
            if(!isset($colMap[$c])) $colMap[$c] = $c;
        }

        if ($format === 'excel') {
            $this->outputExcel("results_export_" . $termId, $data, $columns, $colMap);
        } else {
            $this->outputCSV("results_export_" . $termId, $data, $columns, $colMap);
        }
    }

    private function exportStaff($status, array $columns, string $format): void {
        $params = ['teacher'];
        $where = ["u.role = ?"];

        if ($status === 'active') {
            $where[] = "u.is_active = 1";
        }

        $whereClause = implode(' AND ', $where);

        $query = "
            SELECT 
                u.full_name,
                u.gender,
                u.email,
                u.phone,
                u.role,
                (SELECT GROUP_CONCAT(c.class_name SEPARATOR ', ')
                 FROM class_teachers ct
                 JOIN classes c ON c.id = ct.class_id
                 WHERE ct.teacher_id = u.id) as assigned_classes
            FROM users u
            WHERE {$whereClause}
            ORDER BY u.full_name ASC
        ";

        $data = DB::query($query, $params);

        $colMap = [
            'full_name'        => 'Full Name',
            'gender'           => 'Gender',
            'email'            => 'Email Address',
            'phone'            => 'Phone Number',
            'role'             => 'Role',
            'assigned_classes' => 'Assigned Classes'
        ];

        if ($format === 'excel') {
            $this->outputExcel("staff_export", $data, $columns, $colMap);
        } else {
            $this->outputCSV("staff_export", $data, $columns, $colMap);
        }
    }

    private function exportAttendance($classId, int $termId, $status, array $columns, string $format): void {
        $params = [$termId];
        $where = ["a.term_id = ?"];

        if ($classId !== 'all') {
            $where[] = "s.current_class_id = ?";
            $params[] = $classId;
        }

        if ($status === 'active') {
            $where[] = "s.status = 'active'";
        }

        $whereClause = implode(' AND ', $where);

        // Fetch term explicitly to get total days
        $termInfo = DB::queryOne("SELECT total_school_days FROM terms WHERE id = ?", [$termId]);
        $totalDays = $termInfo['total_school_days'] ?? 0;

        $query = "
            SELECT 
                s.student_id_number,
                s.full_name,
                c.class_name,
                a.days_present
            FROM attendance a
            JOIN students s ON s.id = a.student_id
            JOIN classes c ON c.id = s.current_class_id
            WHERE {$whereClause}
            ORDER BY c.class_name ASC, s.gender ASC, s.full_name ASC
        ";

        $data = DB::query($query, $params);

        foreach ($data as &$row) {
            if (in_array('days_absent', $columns)) {
                // Ensure no negative absenteeism
                $row['days_absent'] = max(0, $totalDays - (int)$row['days_present']);
            }
        }

        $colMap = [
            'student_id_number' => 'Student ID',
            'full_name' => 'Full Name',
            'class_name' => 'Classroom',
            'days_present' => 'Days Present',
            'days_absent' => 'Days Absent'
        ];

        if ($format === 'excel') {
            $this->outputExcel("attendance_export", $data, $columns, $colMap);
        } else {
            $this->outputCSV("attendance_export", $data, $columns, $colMap);
        }
    }

    private function exportSmsLogs(array $columns, string $format): void {
        $query = "
            SELECT
                DATE_FORMAT(sl.sent_at, '%d-%b-%Y %H:%i') AS sent_at,
                sl.sms_type,
                sl.recipient_phone,
                sl.status,
                LEFT(sl.message, 160)                     AS message
            FROM sms_logs sl
            ORDER BY sl.sent_at DESC
        ";

        $data = DB::query($query);

        $colMap = [
            'sent_at'         => 'Date & Time Sent',
            'sms_type'        => 'Message Type',
            'recipient_phone' => 'Recipient Phone',
            'status'          => 'Delivery Status',
            'message'         => 'Message Preview (160 chars)',
        ];

        if ($format === 'excel') {
            $this->outputExcel('sms_history_export', $data, $columns, $colMap);
        } else {
            $this->outputCSV('sms_history_export', $data, $columns, $colMap);
        }
    }

    private function outputExcel(string $filenamePrefix, array $data, array $selectedColumns, array $columnMapping): void {
        $date = date('Ymd_His');
        $filename = "{$filenamePrefix}_{$date}.xls";


        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $widths = $this->calculateColumnWidths($data, $selectedColumns, $columnMapping);

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        echo '<Styles><Style ss:ID="sHeader"><Font ss:Bold="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style></Styles>' . "\n";
        echo '<Worksheet ss:Name="Export"><Table>' . "\n";

        // 1. Column Widths
        foreach ($widths as $w) {
            echo '<Column ss:Width="' . ($w * 7) . '"/>' . "\n";
        }

        // 2. Headers
        echo '<Row ss:StyleID="sHeader">' . "\n";
        foreach ($selectedColumns as $col) {
            $header = $columnMapping[$col] ?? $col;
            // Clean up raw database keys as final fallback
            if ($header === $col) {
                $header = str_replace('_', ' ', $header);
                $header = ucwords($header);
            }
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>' . "\n";
        }
        echo '</Row>' . "\n";

        // 3. Data
        foreach ($data as $row) {
            echo '<Row>' . "\n";
            foreach ($selectedColumns as $col) {
                $val = isset($row[$col]) ? $row[$col] : '';
                $type = is_numeric($val) && !str_starts_with($val, '0') ? 'Number' : 'String';
                echo '<Cell><Data ss:Type="' . $type . '">' . htmlspecialchars($val) . '</Data></Cell>' . "\n";
            }
            echo '</Row>' . "\n";
        }

        echo '</Table></Worksheet></Workbook>' . "\n";
    }

    private function calculateColumnWidths(array $data, array $columns, array $mapping): array {
        $widths = [];
        foreach ($columns as $idx => $col) {
            // Header length
            $header = $mapping[$col] ?? $col;
            $maxLen = strlen($header);
            
            // Sample first 100 rows for performance
            $sample = array_slice($data, 0, 100);
            foreach ($sample as $row) {
                $val = (string)($row[$col] ?? '');
                if (strlen($val) > $maxLen) $maxLen = strlen($val);
            }
            
            // Constrain width (8.5 multiplier is safer for Excel default fonts)
            $widths[$idx] = min(60, max(12, ($maxLen * 8.5) / 7)); 
        }
        return $widths;
    }

    private function outputCSV(string $filenamePrefix, array $data, array $selectedColumns, array $columnMapping): void {
        $date = date('Ymd_His');
        $filename = "{$filenamePrefix}_{$date}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel compatibility
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        // 1. Write Headers based on selected columns
        $headers = [];
        foreach ($selectedColumns as $col) {
            $headers[] = $columnMapping[$col] ?? $col;
        }
        fputcsv($out, $headers);

        // 2. Write Data
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($selectedColumns as $col) {
                // Remove newlines and tabs to prep for CSV just in case
                $val = isset($row[$col]) ? $row[$col] : '';
                $val = str_replace(["\r", "\n", "\t"], ' ', $val);
                $csvRow[] = $val;
            }
            fputcsv($out, $csvRow);
        }

        fclose($out);
    }

    private function redirect(): void {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header("Location: {$base}/admin/export");
        exit;
    }
}
