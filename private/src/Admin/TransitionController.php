<?php
/**
 * Transition Controller — End-of-Term & End-of-Year
 * Handles bulk cloning of classes, assignments, and student status.
 */

class TransitionController {

    public function handle(): void {
        Session::requireRole('admin');

        $uri    = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $path = parse_url($uri, PHP_URL_PATH);

        // ── AJAX: Fetch terms for target year ────────────────────────
        if (isset($_GET['year_id']) && str_ends_with($path, '/terms')) {
            $this->ajaxFetchTerms((int)$_GET['year_id']);
            return;
        }

        if ($method === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Security token mismatch.');
                $this->redirect();
            }
            $action = $_POST['_action'] ?? '';
            match ($action) {
                'term_transition' => $this->termTransition(),
                'year_transition' => $this->yearTransition(),
                default => $this->redirect(),
            };
        }

        // GET: Prep data for dashboard
        global $activeTerm, $academicYears, $termStats;

        $activeTerm = DB::queryOne(
            "SELECT t.*, ay.year_name 
             FROM terms t 
             JOIN academic_years ay ON t.academic_year_id = ay.id 
             WHERE t.is_active = 1 LIMIT 1"
        );

        $academicYears = DB::query("SELECT * FROM academic_years ORDER BY year_name DESC");

        // Simple stats for the current session
        if ($activeTerm) {
            $termStats = [
                'assignments' => DB::queryValue("SELECT COUNT(*) FROM class_subjects WHERE term_id = ?", [$activeTerm['id']]),
                'classes'     => DB::queryValue("SELECT COUNT(*) FROM classes WHERE academic_year_id = ?", [$activeTerm['academic_year_id']]),
                'promoted'    => DB::queryValue("SELECT COUNT(*) FROM student_promotions WHERE academic_year_id = ? AND promotion_status = 'promoted'", [$activeTerm['academic_year_id']]),
            ];
        }
    }

    /**
     * Term-to-Term Transition (Same Year)
     * Clones class assignments (teachers) to the new term.
     */
    private function termTransition(): void {
        $sourceTermId = (int)($_POST['source_term_id'] ?? 0);
        $targetTermId = (int)($_POST['target_term_id'] ?? 0);

        if (!$sourceTermId || !$targetTermId || $sourceTermId === $targetTermId) {
            Session::flash('error', 'Invalid source or target term.');
            $this->redirect();
        }

        DB::beginTransaction();
        try {
            // 1. Get assignments from source
            $assignments = DB::query("SELECT class_id, subject_id, teacher_id FROM class_subjects WHERE term_id = ?", [$sourceTermId]);
            
            $cloned = 0;
            foreach ($assignments as $a) {
                // Only insert if not exists in target
                $exists = DB::queryOne("SELECT id FROM class_subjects WHERE class_id = ? AND subject_id = ? AND term_id = ?", [$a['class_id'], $a['subject_id'], $targetTermId]);
                if (!$exists) {
                    DB::execute(
                        "INSERT INTO class_subjects (class_id, subject_id, teacher_id, term_id) VALUES (?, ?, ?, ?)",
                        [$a['class_id'], $a['subject_id'], $a['teacher_id'], $targetTermId]
                    );
                    $cloned++;
                }
            }

            // 2. Flip active status
            DB::execute("UPDATE terms SET is_active = 0 WHERE id = ?", [$sourceTermId]);
            DB::execute("UPDATE terms SET is_active = 1 WHERE id = ?", [$targetTermId]);

            Session::updateActiveTerm();
            Notification::send(null, "Term Transition Complete", "Successfully transitioned to a new term. {$cloned} subject assignments cloned.", 'success', '/admin/transition');
            Session::flash('success', "Term transition complete! {$cloned} subject assignments cloned to the new term.");
        } catch (Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Transition failed: ' . $e->getMessage());
        }
        $this->redirect();
    }

    /**
     * Year-to-Year Transition (Full Migration)
     * Clones classes, maps students, and clones assignments.
     */
    private function yearTransition(): void {
        $sourceYearId = (int)($_POST['source_year_id'] ?? 0);
        $targetYearId = (int)($_POST['target_year_id'] ?? 0);
        $targetTermId = (int)($_POST['target_term_id'] ?? 0); // Term 1 of new year

        if (!$sourceYearId || !$targetYearId || $sourceYearId === $targetYearId || !$targetTermId) {
            Session::flash('error', 'Invalid source parameters for year transition.');
            $this->redirect();
        }

        DB::beginTransaction();
        try {
            // 1. Clone Classes
            $oldClasses = DB::query("SELECT * FROM classes WHERE academic_year_id = ?", [$sourceYearId]);
            $classMap = []; // old_id => new_id
            
            foreach ($oldClasses as $c) {
                // Check if already exists in target
                $newId = DB::queryValue("SELECT id FROM classes WHERE class_name = ? AND academic_year_id = ?", [$c['class_name'], $targetYearId]);
                if (!$newId) {
                    $newId = DB::insert(
                        "INSERT INTO classes (level_id, class_name, section, academic_year_id) VALUES (?, ?, ?, ?)",
                        [$c['level_id'], $c['class_name'], $c['section'], $targetYearId]
                    );
                }
                $classMap[$c['id']] = (int)$newId;
            }

            // 2. Clone Form Teachers (class_teachers)
            foreach ($classMap as $oldCid => $newCid) {
                $teachers = DB::query("SELECT teacher_id FROM class_teachers WHERE class_id = ?", [$oldCid]);
                foreach ($teachers as $t) {
                    DB::execute("INSERT IGNORE INTO class_teachers (class_id, teacher_id) VALUES (?, ?)", [$newCid, $t['teacher_id']]);
                }
            }

            // 3. Promote Students
            // We look at 'promoted' status in student_promotions for the source year
            $promotions = DB::query("SELECT * FROM student_promotions WHERE academic_year_id = ? AND promotion_status = 'promoted'", [$sourceYearId]);
            $promotedTotal = 0;

            foreach ($promotions as $p) {
                $studentId = $p['student_id'];
                $nextClass = $p['next_class_name'];

                // Find the new class ID by name in the target year
                $targetClassId = DB::queryValue("SELECT id FROM classes WHERE class_name = ? AND academic_year_id = ?", [$nextClass, $targetYearId]);
                
                if ($targetClassId) {
                    DB::execute(
                        "UPDATE students SET academic_year_id = ?, current_class_id = ? WHERE id = ?",
                        [$targetYearId, $targetClassId, $studentId]
                    );
                    $promotedTotal++;
                } else {
                    // Just update year if class not found (manual fix needed later)
                    DB::execute("UPDATE students SET academic_year_id = ? WHERE id = ?", [$targetYearId, $studentId]);
                }
            }

            // 4. Handle Repeaters
            $repeaters = DB::query("SELECT student_id FROM student_promotions WHERE academic_year_id = ? AND promotion_status = 'repeated'", [$sourceYearId]);
            foreach ($repeaters as $r) {
                $oldCid = DB::queryValue("SELECT current_class_id FROM students WHERE id = ?", [$r['student_id']]);
                $oldClassName = DB::queryValue("SELECT class_name FROM classes WHERE id = ?", [$oldCid]);
                $newCid = DB::queryValue("SELECT id FROM classes WHERE class_name = ? AND academic_year_id = ?", [$oldClassName, $targetYearId]);
                
                DB::execute("UPDATE students SET academic_year_id = ?", [$targetYearId]);
                if ($newCid) {
                    DB::execute("UPDATE students SET current_class_id = ? WHERE id = ?", [$newCid, $r['student_id']]);
                }
                DB::execute("UPDATE students SET academic_year_id = ? WHERE id = ?", [$targetYearId, $r['student_id']]);
            }

            // 5. Clone Assignments to Target Term 1
            // Use the last active term of source year as assignment source
            $lastTerm = DB::queryOne("SELECT id FROM terms WHERE academic_year_id = ? OR id = (SELECT term_id FROM student_promotions WHERE academic_year_id = ? LIMIT 1) ORDER BY term_number DESC LIMIT 1", [$sourceYearId, $sourceYearId]);
            if ($lastTerm) {
                $assignments = DB::query("SELECT class_id, subject_id, teacher_id FROM class_subjects WHERE term_id = ?", [$lastTerm['id']]);
                foreach ($assignments as $a) {
                    $newCid = $classMap[$a['class_id']] ?? null;
                    if ($newCid) {
                        DB::execute(
                            "INSERT IGNORE INTO class_subjects (class_id, subject_id, teacher_id, term_id) VALUES (?, ?, ?, ?)",
                            [$newCid, $a['subject_id'], $a['teacher_id'], $targetTermId]
                        );
                    }
                }
            }

            // 6. Flip active status
            DB::execute("UPDATE academic_years SET is_active = 0");
            DB::execute("UPDATE academic_years SET is_active = 1 WHERE id = ?", [$targetYearId]);
            DB::execute("UPDATE terms SET is_active = 0");
            DB::execute("UPDATE terms SET is_active = 1 WHERE id = ?", [$targetTermId]);

            Session::updateActiveTerm();
            Notification::send(null, "Year Transition Complete", "Successfully transitioned to a new academic year. {$promotedTotal} students promoted.", 'success', '/admin/transition');
            Session::flash('success', "Year transition complete! {$promotedTotal} students promoted. Classes and assignments migrated to the new session.");
        } catch (Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Year transition failed: ' . $e->getMessage());
        }
        $this->redirect();
    }

    private function ajaxFetchTerms(int $yearId): never {
        $terms = DB::query("SELECT id, name, term_number FROM terms WHERE academic_year_id = ? ORDER BY term_number ASC", [$yearId]);
        header('Content-Type: application/json');
        echo json_encode(['terms' => $terms]);
        exit;
    }

    private function redirect(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header("Location: {$base}/admin/transition");
        exit;
    }
}
