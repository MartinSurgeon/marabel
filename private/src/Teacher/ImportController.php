<?php
/**
 * Teacher Score Import Controller
 * Uaddara Basic School — SBA Management System
 *
 * Routes:
 *   GET  /teacher/import                  → show upload page
 *   POST /teacher/import?action=template  → download pre-filled CSV roster
 *   POST /teacher/import?action=upload    → validate + process uploaded CSV
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';
require_once PRIVATE_PATH . '/src/Helpers/CSRF.php';
require_once PRIVATE_PATH . '/src/Engine/GradingEngine.php';

class ImportController {

    public function handle(): void {
        Session::requireRole('teacher', 'admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return; // template handles GET
        }

        if (!CSRF::verify()) {
            Session::flash('import_error', 'Invalid request. Please try again.');
            $this->redirect('/teacher/import');
        }

        $action = $_GET['action'] ?? '';

        match ($action) {
            'template' => $this->generateTemplate(),
            'upload'   => $this->processUpload(),
            default    => $this->redirect('/teacher/import'),
        };
    }

    // ── Template Download ─────────────────────────────────────────────
    /**
     * Streams a pre-filled CSV roster for the selected class subject.
     * Teachers fill in the score columns and re-upload.
     */
    private function generateTemplate(): void {
        $csId = (int)($_POST['class_subject_id'] ?? 0);
        if (!$csId) {
            Session::flash('import_error', 'Please select a class and subject first.');
            $this->redirect('/teacher/import');
        }

        $cs = $this->getClassSubjectOrAbort($csId);

        // Fetch active students ordered by surname
        $students = DB::query(
            "SELECT student_id_number, full_name, surname, gender
             FROM students
             WHERE current_class_id = ? AND status = 'active'
             ORDER BY gender ASC, surname ASC, full_name ASC",
            [$cs['class_id']]
        );

        if (empty($students)) {
            Session::flash('import_error', 'No active students found in this class.');
            $this->redirect('/teacher/import');
        }

        // Send CSV headers
        $filename = sprintf(
            '%s %s %s %s Term%d.csv',
            $cs['class_name'],
            $cs['subject_name'],
            $cs['section'] ? $cs['section'] : '',
            date('Ymd'),
            $cs['term_number']
        );
        // Clean up potential double spaces and illegal characters
        $filename = preg_replace('/\s+/', ' ', trim($filename));
        $filename = preg_replace('/[\\/:*?"<>|]/', '', $filename);

        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: no-cache');

        $out = fopen('php://output', 'w');

        // Metadata rows (read-only context for the teacher)
        fputcsv($out, ['# DO NOT EDIT THIS SECTION']);
        fputcsv($out, ['# Class', $cs['class_name'] . ($cs['section'] ? " ({$cs['section']})" : '')]);
        fputcsv($out, ['# Subject', $cs['subject_name']]);
        fputcsv($out, ['# Class Subject ID', $csId]);
        fputcsv($out, ['# Term ID', $cs['term_id']]);
        fputcsv($out, ['# Max per SBA component: 15 | Max exam (raw): 100']);
        fputcsv($out, []);

        // Header row
        fputcsv($out, [
            'Student_ID',
            'Student_Name',
            'Individual_Test_max15',
            'Group_Work_max15',
            'Class_Test_max15',
            'Project_max15',
            'Exam_Raw_max100',
        ]);

        // Pre-fill existing scores if any (so teachers don't lose data)
        $existingSba = DB::query(
            "SELECT student_id, individual_test, group_work, class_test, project
             FROM sba_component_scores
             WHERE class_subject_id = ? AND term_id = ?",
            [$csId, $cs['term_id']]
        );
        $sbaMap = array_column($existingSba, null, 'student_id');

        $existingExam = DB::query(
            "SELECT student_id, raw_score FROM exam_scores
             WHERE class_subject_id = ? AND term_id = ?",
            [$csId, $cs['term_id']]
        );
        $examMap = array_column($existingExam, null, 'student_id');

        foreach ($students as $s) {
            $sid     = $s['student_id_number'];
            $dbRow   = DB::queryOne(
                "SELECT id FROM students WHERE student_id_number = ? AND status = 'active'",
                [$sid]
            );
            $dbId    = $dbRow['id'] ?? null;
            $sba     = $dbId ? ($sbaMap[$dbId]  ?? []) : [];
            $exam    = $dbId ? ($examMap[$dbId] ?? []) : [];

            fputcsv($out, [
                $sid,
                $s['full_name'],
                $sba['individual_test'] ?? '',
                $sba['group_work']      ?? '',
                $sba['class_test']      ?? '',
                $sba['project']         ?? '',
                $exam['raw_score']      ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    // ── CSV Upload + Validation + Save ────────────────────────────────
    private function processUpload(): void {
        $file = $_FILES['score_csv'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::flash('import_error', 'No file uploaded or upload error occurred.');
            $this->redirect('/teacher/import');
        }

        if (!in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), ['csv'])) {
            Session::flash('import_error', 'Only .csv files are accepted.');
            $this->redirect('/teacher/import');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            Session::flash('import_error', 'File exceeds the 5 MB limit.');
            $this->redirect('/teacher/import');
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Session::flash('import_error', 'Could not open the uploaded file.');
            $this->redirect('/teacher/import');
        }

        // ── 1. Parse meta rows to extract csId and termId ────────────
        $csId   = null;
        $termId = null;
        $dataRows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (!isset($row[0])) continue;

            $cell0 = trim($row[0]);

            if ($cell0 === '# Class Subject ID') { $csId   = (int)($row[1] ?? 0); continue; }
            if ($cell0 === '# Term ID')           { $termId = (int)($row[1] ?? 0); continue; }
            if (str_starts_with($cell0, '#'))     continue; // other meta lines
            if ($cell0 === '')                    continue; // blank separator
            if ($cell0 === 'Student_ID')          continue; // header row

            $dataRows[] = $row;
        }
        fclose($handle);

        if (!$csId || !$termId) {
            Session::flash('import_error', 'Invalid template: missing Class Subject ID or Term ID metadata rows. Please download a fresh template.');
            $this->redirect('/teacher/import');
        }

        $cs = $this->getClassSubjectOrAbort($csId);

        // Verify term matches
        if ((int)$cs['term_id'] !== $termId) {
            Session::flash('import_error', 'Term ID mismatch. Download a fresh template for the current term.');
            $this->redirect('/teacher/import');
        }

        // Build a lookup: student_id_number → student DB id
        $students = DB::query(
            "SELECT id, student_id_number FROM students
             WHERE current_class_id = ? AND status = 'active'",
            [$cs['class_id']]
        );
        $studentMap = array_column($students, 'id', 'student_id_number');

        // ── 2. Validate all rows first ─────────────────────────────────
        $errors  = [];
        $valid   = [];
        $lineNum = 1; // after header

        foreach ($dataRows as $row) {
            $lineNum++;
            $studentIdNum = str_pad(trim($row[0] ?? ''), 4, '0', STR_PAD_LEFT);
            $indivTest    = $this->parseScore($row[2] ?? '', 'Individual Test',   15.0, $lineNum, $errors);
            $groupWork    = $this->parseScore($row[3] ?? '', 'Group Work',         15.0, $lineNum, $errors);
            $classTest    = $this->parseScore($row[4] ?? '', 'Class Test',         15.0, $lineNum, $errors);
            $project      = $this->parseScore($row[5] ?? '', 'Project',            15.0, $lineNum, $errors);
            $examRaw      = $this->parseScore($row[6] ?? '', 'Exam',              100.0, $lineNum, $errors);

            if (!isset($studentMap[$studentIdNum])) {
                $errors[] = "Row {$lineNum}: Student ID «{$studentIdNum}» not found in this class.";
                continue;
            }

            $valid[] = [
                'student_id'   => $studentMap[$studentIdNum],
                'indiv_test'   => $indivTest,
                'group_work'   => $groupWork,
                'class_test'   => $classTest,
                'project'      => $project,
                'exam_raw'     => $examRaw,
            ];
        }

        // If ANY validation error, abort — don't save partial data
        if (!empty($errors)) {
            Session::set('import_errors', $errors);
            Session::flash('import_error', 'Validation failed. No scores were saved. Fix the errors and re-upload.');
            $this->redirect('/teacher/import');
        }

        // ── 3. Save validated rows ─────────────────────────────────────
        $saved  = 0;
        $failed = [];

        foreach ($valid as $r) {
            try {
                $this->upsertSba($r['student_id'], $csId, $termId, $r['indiv_test'], $r['group_work'], $r['class_test'], $r['project']);
                if ($r['exam_raw'] !== null) {
                    $this->upsertExam($r['student_id'], $csId, $termId, $r['exam_raw']);
                }
                $saved++;
            } catch (\Throwable $e) {
                $failed[] = "Student DB ID {$r['student_id']}: " . $e->getMessage();
            }
        }

        if (!empty($failed)) {
            Session::set('import_errors', $failed);
            Session::flash('import_warn', "{$saved} rows saved. " . count($failed) . " row(s) failed to write (see below).");
        } else {
            Session::flash('import_success', "✓ Successfully imported scores for {$saved} student(s) into {$cs['subject_name']} ({$cs['class_name']}).");
        }

        $this->redirect('/teacher/import');
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function getClassSubjectOrAbort(int $csId): array {
        $cs = DB::queryOne(
            "SELECT cs.id, cs.class_id, cs.term_id, c.class_name, c.section, s.subject_name, cs.teacher_id,
                    t.term_number
             FROM class_subjects cs
             JOIN classes c ON c.id = cs.class_id
             JOIN subjects s ON s.id = cs.subject_id
             JOIN terms t ON t.id = cs.term_id
             WHERE cs.id = ?",
            [$csId]
        );

        if (!$cs) {
            Session::flash('import_error', 'Class/subject assignment not found.');
            $this->redirect('/teacher/import');
        }

        // Teachers may only access their own assignments
        if (Session::role() === 'teacher' && $cs['teacher_id'] != Session::userId()) {
            Session::flash('import_error', 'You are not assigned to this class/subject.');
            $this->redirect('/teacher/import');
        }

        return $cs;
    }

    /**
     * Parse and validate a score cell. Returns float|null.
     * Appends to $errors on failure.
     */
    private function parseScore(string $raw, string $fieldName, float $max, int $lineNum, array &$errors): ?float {
        $raw = trim($raw);
        if ($raw === '' || strtolower($raw) === 'n/a') return null;

        if (!is_numeric($raw)) {
            $errors[] = "Row {$lineNum} — {$fieldName}: «{$raw}» is not a number.";
            return null;
        }

        $val = (float)$raw;
        if ($val < 0 || $val > $max) {
            $errors[] = "Row {$lineNum} — {$fieldName}: {$val} is out of range (0–{$max}).";
        }

        return round($val, 2);
    }

    /**
     * Upsert SBA component scores and recalculate totals.
     */
    private function upsertSba(int $studentId, int $csId, int $termId, ?float $indiv, ?float $group, ?float $classTest, ?float $project): void {
        $exists = DB::queryOne(
            "SELECT id, individual_test, group_work, class_test, project FROM sba_component_scores
             WHERE student_id = ? AND class_subject_id = ? AND term_id = ?",
            [$studentId, $csId, $termId]
        );

        // Merge: only overwrite columns that have a new value; keep existing if null
        $newIndiv = $indiv      ?? ($exists['individual_test'] ?? null);
        $newGroup = $group      ?? ($exists['group_work']      ?? null);
        $newClass = $classTest  ?? ($exists['class_test']      ?? null);
        $newProj  = $project    ?? ($exists['project']         ?? null);

        $subTotal   = (float)$newIndiv + (float)$newGroup + (float)$newClass + (float)$newProj;
        $classScore = round(($subTotal / 60) * 50, 2);

        if ($exists) {
            DB::execute(
                "UPDATE sba_component_scores
                 SET individual_test = ?, group_work = ?, class_test = ?, project = ?,
                     sub_total = ?, class_score = ?, updated_at = NOW(), entered_by = ?
                 WHERE id = ?",
                [$newIndiv, $newGroup, $newClass, $newProj, $subTotal, $classScore, Session::userId(), $exists['id']]
            );
        } else {
            DB::execute(
                "INSERT INTO sba_component_scores
                 (student_id, class_subject_id, term_id, individual_test, group_work, class_test, project, sub_total, class_score, entered_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$studentId, $csId, $termId, $newIndiv, $newGroup, $newClass, $newProj, $subTotal, $classScore, Session::userId()]
            );
        }
    }

    /**
     * Upsert exam score.
     */
    private function upsertExam(int $studentId, int $csId, int $termId, ?float $rawScore): void {
        $examScaled = $rawScore !== null ? round(($rawScore / 100) * 50, 2) : null;

        $exists = DB::queryOne(
            "SELECT id FROM exam_scores WHERE student_id = ? AND class_subject_id = ? AND term_id = ?",
            [$studentId, $csId, $termId]
        );

        if ($exists) {
            DB::execute(
                "UPDATE exam_scores SET raw_score = ?, exam_score = ?, updated_at = NOW(), entered_by = ?
                 WHERE id = ?",
                [$rawScore, $examScaled, Session::userId(), $exists['id']]
            );
        } else {
            DB::execute(
                "INSERT INTO exam_scores (student_id, class_subject_id, term_id, raw_score, exam_score, entered_by)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$studentId, $csId, $termId, $rawScore, $examScaled, Session::userId()]
            );
        }
    }

    private function redirect(string $path): never {
        header('Location: ' . (defined('APP_BASE') ? APP_BASE : '') . $path);
        exit;
    }
}
