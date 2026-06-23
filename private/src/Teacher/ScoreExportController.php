<?php
/**
 * Score Export Controller
 * Uaddara Basic School — SBA Management System
 *
 * Routes:
 *   GET /teacher/export-scores?id=<csId>&format=pdf             → Simplified portrait print (default)
 *   GET /teacher/export-scores?id=<csId>&format=pdf&layout=full → Full landscape SBA sheet
 *   GET /teacher/export-scores?id=all&format=pdf                → Bulk print all assigned subjects
 *   GET /teacher/export-scores?id=<csId>&format=csv             → CSV download
 *   GET /teacher/export-scores?id=<csId>&format=excel           → SpreadsheetML .xls download
 *
 * Access: admin (all classes) | teacher (own assigned class/subject only)
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';

class ScoreExportController {

    public function handle(): void {
        Session::requireRole('teacher', 'admin');

        $idParam = trim($_GET['id'] ?? '');
        $format  = strtolower(trim($_GET['format'] ?? 'pdf'));
        $layout  = strtolower(trim($_GET['layout'] ?? 'simplified'));

        // ── Bulk print: id=all ─────────────────────────────────────────
        if ($idParam === 'all') {
            if ($format !== 'pdf') {
                $this->abort('Bulk export is only available as a printable class list. Please export subjects individually for CSV/Excel.');
            }
            $this->streamBulkPdf($layout);
            return; // unreachable but safe
        }

        // ── Single subject ─────────────────────────────────────────────
        $csId = (int)$idParam;
        if (!$csId) {
            $this->abort('No class/subject selected.');
        }

        $cs = $this->getClassSubjectOrAbort($csId);
        [$students, $sbaMap, $examMap] = $this->fetchScores($cs);

        match ($format) {
            'csv'   => $this->streamCsv($cs, $students, $sbaMap, $examMap),
            'excel' => $this->streamExcel($cs, $students, $sbaMap, $examMap),
            default => $this->streamPdf($cs, $students, $sbaMap, $examMap, $layout),
        };
    }

    // ── Data Retrieval ─────────────────────────────────────────────

    private function getClassSubjectOrAbort(int $csId): array {
        $cs = DB::queryOne(
            "SELECT cs.id, cs.class_id, cs.term_id, cs.teacher_id,
                    c.class_name, c.section,
                    s.subject_name,
                    t.term_number, t.name as term_name,
                    ay.year_name
             FROM class_subjects cs
             JOIN classes c   ON c.id  = cs.class_id
             JOIN subjects s  ON s.id  = cs.subject_id
             JOIN terms t     ON t.id  = cs.term_id
             JOIN academic_years ay ON ay.id = t.academic_year_id
             WHERE cs.id = ?",
            [$csId]
        );

        if (!$cs) {
            $this->abort('Class/subject assignment not found.');
        }

        // Teachers may only access their own assignments
        if (Session::role() === 'teacher' && $cs['teacher_id'] != Session::userId()) {
            $this->abort('You are not assigned to this class/subject.');
        }

        return $cs;
    }

    private function fetchScores(array $cs): array {
        $students = DB::query(
            "SELECT id, student_id_number, full_name, gender
             FROM students
             WHERE current_class_id = ? AND status = 'active'
             ORDER BY gender ASC, surname ASC, full_name ASC",
            [$cs['class_id']]
        );

        $sbaRows = DB::query(
            "SELECT student_id, class_test, group_work, project, individual_test, sub_total, class_score
             FROM sba_component_scores
             WHERE class_subject_id = ? AND term_id = ?",
            [$cs['id'], $cs['term_id']]
        );
        $sbaMap = array_column($sbaRows, null, 'student_id');

        $examRows = DB::query(
            "SELECT student_id, raw_score, exam_score
             FROM exam_scores
             WHERE class_subject_id = ? AND term_id = ?",
            [$cs['id'], $cs['term_id']]
        );
        $examMap = array_column($examRows, null, 'student_id');

        return [$students, $sbaMap, $examMap];
    }

    /**
     * Fetch all assignments for the current teacher (or all for admin) in the active term.
     */
    private function fetchAllAssignments(): array {
        $term = DB::queryOne(
            "SELECT t.id, t.name as term_name, t.term_number, ay.year_name
             FROM terms t
             JOIN academic_years ay ON ay.id = t.academic_year_id
             WHERE t.is_active = 1 LIMIT 1"
        );

        if (!$term) return [];

        $isAdmin   = (Session::role() === 'admin');
        $teacherId = Session::userId();

        $where  = $isAdmin ? "cs.term_id = ?" : "cs.teacher_id = ? AND cs.term_id = ?";
        $params = $isAdmin ? [$term['id']] : [$teacherId, $term['id']];

        $rows = DB::query(
            "SELECT cs.id, cs.class_id, cs.term_id, cs.teacher_id,
                    c.class_name, c.section,
                    s.subject_name,
                    t.term_number, t.name as term_name,
                    ay.year_name
             FROM class_subjects cs
             JOIN classes c   ON c.id  = cs.class_id
             JOIN subjects s  ON s.id  = cs.subject_id
             JOIN terms t     ON t.id  = cs.term_id
             JOIN academic_years ay ON ay.id = t.academic_year_id
             WHERE {$where}
             ORDER BY c.class_name ASC, s.sort_order ASC",
            $params
        );

        return $rows;
    }

    // ── Helpers ────────────────────────────────────────────────────

    /** Build a clean filename base (no illegal chars, no double spaces). */
    private function buildFilename(array $cs, string $ext): string {
        $class   = $cs['class_name'] . ($cs['section'] ? " {$cs['section']}" : '');
        $subject = $cs['subject_name'];
        $term    = "Term {$cs['term_number']}";
        $date    = date('Ymd');

        $raw = "{$class} {$subject} {$term} Scores {$date}.{$ext}";
        $raw = preg_replace('#\s+#', ' ', trim($raw));
        $raw = preg_replace('#[\\\\/:*?"<>|]#', '', $raw);

        return $raw ?? '';
    }

    /** Format a score value safely: returns '' when null. */
    private function fmt(mixed $val): string {
        if ($val === null || $val === '') return '';
        $f = (float)$val;
        return ($f == (int)$f) ? (string)(int)$f : number_format($f, 2);
    }

    private function abort(string $msg): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        Session::flash('error', $msg);
        header("Location: {$base}/teacher/scores");
        exit;
    }

    // ── Shared Print Header ────────────────────────────────────────

    private function buildPrintStyles(string $layout): string {
        $pageSize = ($layout === 'full') ? 'A4 landscape' : 'A4 portrait';
        return <<<CSS
        body  { font-family: Arial, sans-serif; font-size: 9pt; margin: 20px; }
        h2    { font-size: 13pt; margin: 0 0 3px; font-weight: 900; }
        p     { font-size: 9pt; margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px 6px; text-align: center; font-size: 8.5pt; }
        th    { background: #f0f0f0; font-weight: bold; }
        td.name-cell { text-align: left; }
        tr:nth-child(even) td { background: #fafafa; }
        .no-print { display: block; }
        @media print {
          .no-print { display: none !important; }
          .page-break { page-break-after: always; }
          @page { size: {$pageSize}; margin: 10mm; }
        }
CSS;
    }

    private function buildPrintHeader(array $cs): string {
        $schoolName = htmlspecialchars(\Config::get('school_name', 'Uaddara Basic School'));
        $class      = htmlspecialchars($cs['class_name'] . ($cs['section'] ? " ({$cs['section']})" : ''));
        $subject    = htmlspecialchars($cs['subject_name']);
        $term       = htmlspecialchars($cs['term_name']);
        $year       = htmlspecialchars($cs['year_name']);
        $generated  = date('d M Y, g:i A');
        return <<<HTML
<h2>{$schoolName}</h2>
<p><strong>Class:</strong> {$class} &nbsp;&nbsp; <strong>Subject:</strong> {$subject}</p>
<p><strong>Term:</strong> {$term} &nbsp;&nbsp; <strong>Academic Year:</strong> {$year}</p>
<p><strong>Generated:</strong> {$generated}</p>
HTML;
    }

    // ── Simplified Portrait Table ──────────────────────────────────

    /**
     * Builds the simplified table HTML (portrait): Name/ID, Indiv. Assignment, Project, Class Test, Exam.
     */
    private function buildSimplifiedTable(array $students, array $sbaMap, array $examMap): string {
        $v = fn($x) => $x !== null && $x !== '' ? $this->fmt($x) : '';

        $rows = '';
        foreach ($students as $i => $s) {
            $sid  = $s['id'];
            $sba  = $sbaMap[$sid]  ?? [];
            $exam = $examMap[$sid] ?? [];
            $rows .= '<tr>';
            $rows .= '<td>' . ($i + 1) . '</td>';
            $rows .= '<td class="name-cell"><strong>' . htmlspecialchars($s['full_name']) . '</strong><br><small style="color:#555;">' . htmlspecialchars($s['student_id_number']) . '</small></td>';
            $rows .= '<td>' . $v($sba['individual_test'] ?? null) . '</td>';
            $rows .= '<td>' . $v($sba['project']         ?? null) . '</td>';
            $rows .= '<td>' . $v($sba['class_test']      ?? null) . '</td>';
            $rows .= '<td>' . $v($exam['raw_score']      ?? null) . '</td>';
            $rows .= '</tr>' . "\n";
        }

        $total = count($students);
        return <<<HTML
<p style="margin-top:8px; font-size:8.5pt;"><strong>Total Students:</strong> {$total}</p>
<table>
  <thead>
    <tr>
      <th style="width:30px;">#</th>
      <th style="text-align:left; width:220px;">Student Name &amp; ID</th>
      <th>Indiv. Assignment<br><small>(max 15)</small></th>
      <th>Project Work<br><small>(max 15)</small></th>
      <th>Class Test<br><small>(max 15)</small></th>
      <th>Exam Score<br><small>(max 100)</small></th>
    </tr>
  </thead>
  <tbody>
    {$rows}
  </tbody>
</table>
HTML;
    }

    // ── Full Landscape Table ───────────────────────────────────────

    private function buildFullTable(array $students, array $sbaMap, array $examMap): string {
        $v = fn($x) => $x !== null && $x !== '' ? $this->fmt($x) : '';

        $rows = '';
        foreach ($students as $i => $s) {
            $sid        = $s['id'];
            $sba        = $sbaMap[$sid]  ?? [];
            $exam       = $examMap[$sid] ?? [];
            $classScore = $sba['class_score']  ?? null;
            $examScore  = $exam['exam_score']   ?? null;
            $overall    = ($classScore !== null || $examScore !== null)
                ? (int)round((float)$classScore + (float)$examScore, 0) : '';

            $rows .= '<tr>';
            $rows .= '<td>' . ($i + 1) . '</td>';
            $rows .= '<td class="name-cell">' . htmlspecialchars($s['full_name']) . '<br><small>' . htmlspecialchars($s['student_id_number']) . '</small></td>';
            $rows .= '<td>' . htmlspecialchars($s['gender'] ?? '') . '</td>';
            $rows .= '<td>' . $v($sba['class_test']      ?? null) . '</td>';
            $rows .= '<td>' . $v($sba['group_work']       ?? null) . '</td>';
            $rows .= '<td>' . $v($sba['project']          ?? null) . '</td>';
            $rows .= '<td>' . $v($sba['individual_test']  ?? null) . '</td>';
            $rows .= '<td>' . $v($sba['sub_total']        ?? null) . '</td>';
            $rows .= '<td>' . $v($classScore)                      . '</td>';
            $rows .= '<td>' . $v($exam['raw_score']       ?? null) . '</td>';
            $rows .= '<td>' . $v($examScore)                       . '</td>';
            $rows .= '<td>' . ($overall !== '' ? $overall : '')    . '</td>';
            $rows .= '</tr>' . "\n";
        }

        $total = count($students);
        return <<<HTML
<p style="margin-top:8px; font-size:8.5pt;"><strong>Total Students:</strong> {$total}</p>
<table>
  <thead>
    <tr>
      <th rowspan="2">#</th>
      <th rowspan="2" style="text-align:left;">Student Name</th>
      <th rowspan="2">Sex</th>
      <th colspan="4">SBA Components (max 15 each)</th>
      <th colspan="2">SBA</th>
      <th colspan="2">Exam</th>
      <th rowspan="2">Total (100)</th>
    </tr>
    <tr>
      <th>Class Test</th>
      <th>Group Work</th>
      <th>Project</th>
      <th>Indiv. Test</th>
      <th>/60</th>
      <th>50%</th>
      <th>/100</th>
      <th>50%</th>
    </tr>
  </thead>
  <tbody>
    {$rows}
  </tbody>
</table>
HTML;
    }

    // ── PDF (single subject) ───────────────────────────────────────

    private function streamPdf(array $cs, array $students, array $sbaMap, array $examMap, string $layout): never {
        $subject   = htmlspecialchars($cs['subject_name']);
        $class     = htmlspecialchars($cs['class_name'] . ($cs['section'] ? " ({$cs['section']})" : ''));
        $styles    = $this->buildPrintStyles($layout);
        $metaBlock = $this->buildPrintHeader($cs);
        $tableHtml = ($layout === 'full')
            ? $this->buildFullTable($students, $sbaMap, $examMap)
            : $this->buildSimplifiedTable($students, $sbaMap, $examMap);

        $layoutLabel = ($layout === 'full') ? 'Full Score Sheet' : 'Class List';

        header('Content-Type: text/html; charset=UTF-8');
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{$subject} — {$class} {$layoutLabel}</title>
  <style>{$styles}</style>
</head>
<body>

<div class="no-print" style="margin-bottom:14px;">
  <button onclick="window.print()" style="padding:8px 18px; font-size:12px; cursor:pointer; background:#6d28d9; color:white; border:none; border-radius:6px; font-weight:700;">🖨 Print / Save PDF</button>
  <button onclick="window.close()" style="padding:8px 18px; font-size:12px; cursor:pointer; margin-left:8px; border:1px solid #ccc; border-radius:6px;">Close</button>
</div>

{$metaBlock}
{$tableHtml}

<script>
  window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });
</script>
</body>
</html>
HTML;
        exit;
    }

    // ── Bulk PDF (all assigned subjects) ──────────────────────────

    private function streamBulkPdf(string $layout): never {
        $assignments = $this->fetchAllAssignments();

        if (empty($assignments)) {
            $this->abort('No subject assignments found for the current active term.');
        }

        $styles    = $this->buildPrintStyles($layout);
        $layoutLabel = ($layout === 'full') ? 'Full Score Sheets' : 'Class Lists';
        $schoolName  = htmlspecialchars(\Config::get('school_name', 'Uaddara Basic School'));
        $generated   = date('d M Y, g:i A');

        $bodyParts = [];

        foreach ($assignments as $cs) {
            [$students, $sbaMap, $examMap] = $this->fetchScores($cs);

            $metaBlock = $this->buildPrintHeader($cs);
            $tableHtml = ($layout === 'full')
                ? $this->buildFullTable($students, $sbaMap, $examMap)
                : $this->buildSimplifiedTable($students, $sbaMap, $examMap);

            $bodyParts[] = "<div class=\"subject-block\">{$metaBlock}{$tableHtml}</div>";
        }

        $count    = count($bodyParts);
        $bodyHtml = '';
        foreach ($bodyParts as $idx => $part) {
            $bodyHtml .= $part;
            if ($idx < $count - 1) {
                // Page break between subjects — visible on screen as a divider
                $bodyHtml .= '<div class="page-break" style="border-top:3px dashed #bbb; margin:24px 0;"></div>';
            }
        }

        header('Content-Type: text/html; charset=UTF-8');
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{$schoolName} — All {$layoutLabel}</title>
  <style>{$styles}</style>
</head>
<body>

<div class="no-print" style="margin-bottom:14px; padding:12px; background:#f5f3ff; border-radius:8px; display:flex; align-items:center; gap:12px;">
  <div>
    <strong style="font-size:13px; color:#6d28d9;">Bulk Class List Print</strong>
    <span style="font-size:11px; color:#555; margin-left:8px;">{$count} subject(s) · Generated {$generated}</span>
  </div>
  <button onclick="window.print()" style="padding:8px 18px; font-size:12px; cursor:pointer; background:#6d28d9; color:white; border:none; border-radius:6px; font-weight:700; margin-left:auto;">🖨 Print All / Save PDF</button>
  <button onclick="window.close()" style="padding:8px 18px; font-size:12px; cursor:pointer; border:1px solid #ccc; border-radius:6px;">Close</button>
</div>

{$bodyHtml}

<script>
  window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 500); });
</script>
</body>
</html>
HTML;
        exit;
    }

    // ── CSV Download ───────────────────────────────────────────────

    private function streamCsv(array $cs, array $students, array $sbaMap, array $examMap): never {
        $filename = $this->buildFilename($cs, 'csv');

        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');

        // UTF-8 BOM for Excel compatibility
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Metadata rows
        fputcsv($out, ['# School', \Config::get('school_name', 'Uaddara Basic School')]);
        fputcsv($out, ['# Class', $cs['class_name'] . ($cs['section'] ? " ({$cs['section']})" : '')]);
        fputcsv($out, ['# Subject', $cs['subject_name']]);
        fputcsv($out, ['# Term', $cs['term_name']]);
        fputcsv($out, ['# Academic Year', $cs['year_name']]);
        fputcsv($out, ['# Generated', date('d M Y, g:i A')]);
        fputcsv($out, []);

        // Headers
        fputcsv($out, [
            '#', 'Student ID', 'Student Name', 'Gender',
            'Class Test (15)', 'Group Work (15)', 'Project (15)', 'Indiv. Test (15)',
            'SBA Total (60)', 'SBA (50%)',
            'Exam Raw (100)', 'Exam (50%)',
            'Total (100)',
        ]);

        foreach ($students as $i => $s) {
            $sid  = $s['id'];
            $sba  = $sbaMap[$sid]  ?? [];
            $exam = $examMap[$sid] ?? [];

            $classScore = $sba['class_score'] ?? null;
            $examScore  = $exam['exam_score']  ?? null;
            $overall    = ($classScore !== null || $examScore !== null)
                ? round((float)$classScore + (float)$examScore, 0)
                : '';

            fputcsv($out, [
                $i + 1,
                $s['student_id_number'],
                $s['full_name'],
                $s['gender'] ?? '',
                $this->fmt($sba['class_test']     ?? null),
                $this->fmt($sba['group_work']      ?? null),
                $this->fmt($sba['project']         ?? null),
                $this->fmt($sba['individual_test'] ?? null),
                $this->fmt($sba['sub_total']       ?? null),
                $this->fmt($classScore),
                $this->fmt($exam['raw_score']      ?? null),
                $this->fmt($examScore),
                $overall !== '' ? (int)$overall : '',
            ]);
        }

        fclose($out);
        exit;
    }

    // ── Excel (SpreadsheetML) Download ────────────────────────────

    private function streamExcel(array $cs, array $students, array $sbaMap, array $examMap): never {
        $filename = $this->buildFilename($cs, 'xls');
        $school   = htmlspecialchars(\Config::get('school_name', 'Uaddara Basic School'));
        $class    = htmlspecialchars($cs['class_name'] . ($cs['section'] ? " ({$cs['section']})" : ''));
        $subject  = htmlspecialchars($cs['subject_name']);
        $term     = htmlspecialchars($cs['term_name']);
        $year     = htmlspecialchars($cs['year_name']);
        $generated = date('d M Y, g:i A');

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
           . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
           . ' xmlns:x="urn:schemas-microsoft-com:office:excel"'
           . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";

        echo '<Styles>'
           . '<Style ss:ID="sTitle"><Font ss:Bold="1" ss:Size="14"/></Style>'
           . '<Style ss:ID="sMeta"><Font ss:Italic="1" ss:Color="#777777"/></Style>'
           . '<Style ss:ID="sHeader"><Font ss:Bold="1"/><Interior ss:Color="#6D28D9" ss:Pattern="Solid"/><Font ss:Color="#FFFFFF" ss:Bold="1"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>'
           . '<Style ss:ID="sSubHeader"><Font ss:Bold="1"/><Interior ss:Color="#EDE9FE" ss:Pattern="Solid"/><Font ss:Color="#4C1D95" ss:Bold="1"/></Style>'
           . '<Style ss:ID="sNumber"><NumberFormat ss:Format="0.##"/></Style>'
           . '<Style ss:ID="sBold"><Font ss:Bold="1"/></Style>'
           . '<Style ss:ID="sTotal"><Font ss:Bold="1" ss:Size="11"/><Interior ss:Color="#F5F3FF" ss:Pattern="Solid"/></Style>'
           . '</Styles>' . "\n";

        echo '<Worksheet ss:Name="Score List"><Table>' . "\n";

        // Column widths
        $widths = [30, 70, 160, 50, 70, 70, 70, 70, 70, 70, 70, 70, 70];
        foreach ($widths as $w) {
            echo "<Column ss:Width=\"{$w}\"/>\n";
        }

        // Metadata rows
        echo "<Row><Cell ss:StyleID=\"sTitle\"><Data ss:Type=\"String\">{$school} — Score List</Data></Cell></Row>\n";
        echo "<Row><Cell ss:StyleID=\"sMeta\"><Data ss:Type=\"String\">{$class} · {$subject} · {$term} · {$year}</Data></Cell></Row>\n";
        echo "<Row><Cell ss:StyleID=\"sMeta\"><Data ss:Type=\"String\">Generated: {$generated}</Data></Cell></Row>\n";
        echo "<Row/>\n";

        // Header row
        $headers = [
            '#', 'Student ID', 'Student Name', 'Gender',
            'Class Test (15)', 'Group Work (15)', 'Project (15)', 'Indiv. Test (15)',
            'SBA Total (60)', 'SBA (50%)',
            'Exam Raw (100)', 'Exam (50%)',
            'Total (100)',
        ];
        echo "<Row>\n";
        foreach ($headers as $h) {
            echo '<Cell ss:StyleID="sHeader"><Data ss:Type="String">' . htmlspecialchars($h) . "</Data></Cell>\n";
        }
        echo "</Row>\n";

        // Data rows
        foreach ($students as $i => $s) {
            $sid  = $s['id'];
            $sba  = $sbaMap[$sid]  ?? [];
            $exam = $examMap[$sid] ?? [];

            $classScore = $sba['class_score'] ?? null;
            $examScore  = $exam['exam_score']  ?? null;
            $overall    = ($classScore !== null || $examScore !== null)
                ? (int)round((float)$classScore + (float)$examScore, 0)
                : null;

            echo "<Row>\n";
            $this->xlsCell($i + 1, 'Number');
            $this->xlsCell($s['student_id_number'], 'String');
            $this->xlsCell($s['full_name'], 'String', 'sBold');
            $this->xlsCell($s['gender'] ?? '', 'String');
            $this->xlsCell($sba['class_test']     ?? null, 'Number');
            $this->xlsCell($sba['group_work']      ?? null, 'Number');
            $this->xlsCell($sba['project']         ?? null, 'Number');
            $this->xlsCell($sba['individual_test'] ?? null, 'Number');
            $this->xlsCell($sba['sub_total']       ?? null, 'Number', 'sBold');
            $this->xlsCell($classScore,               'Number', 'sBold');
            $this->xlsCell($exam['raw_score']      ?? null, 'Number');
            $this->xlsCell($examScore,                'Number', 'sBold');
            $this->xlsCell($overall,                  'Number', 'sTotal');
            echo "</Row>\n";
        }

        echo '</Table></Worksheet></Workbook>' . "\n";
        exit;
    }

    /** Emit a single SpreadsheetML Cell. */
    private function xlsCell(mixed $val, string $type = 'String', string $style = ''): void {
        if ($val === null || $val === '') {
            echo "<Cell><Data ss:Type=\"String\"></Data></Cell>\n";
            return;
        }
        $styleAttr = $style ? " ss:StyleID=\"{$style}\"" : '';
        $escaped   = htmlspecialchars((string)$val);
        echo "<Cell{$styleAttr}><Data ss:Type=\"{$type}\">{$escaped}</Data></Cell>\n";
    }
}
