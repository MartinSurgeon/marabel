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

            DB::commit();
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
                // Check if already exists in target (matching section)
                $sec = $c['section'] ?? '';
                $newId = DB::queryValue(
                    "SELECT id FROM classes WHERE class_name = ? AND (section = ? OR (section IS NULL AND ? = '')) AND academic_year_id = ?",
                    [$c['class_name'], $sec, $sec, $targetYearId]
                );
                if (!$newId) {
                    $newId = DB::insert(
                        "INSERT INTO classes (level_id, class_name, section, academic_year_id, grading_system) VALUES (?, ?, ?, ?, ?)",
                        [$c['level_id'], $c['class_name'], $c['section'], $targetYearId, $c['grading_system'] ?? 'proficiency']
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

            // 3 + 4. Place promoted and repeating students.
            // The promotion step already chose each student's class (target_class_id);
            // honour it so sections are never merged. Graduates stay in their final class.
            $rows = DB::query(
                "SELECT sp.student_id, sp.promotion_status, sp.target_class_id, sp.next_class_name, sp.from_class_id
                 FROM student_promotions sp
                 WHERE sp.academic_year_id = ? AND sp.promotion_status IN ('promoted','repeated')
                 ORDER BY sp.set_at, sp.id",
                [$sourceYearId]
            );
            $latest = [];
            foreach ($rows as $p) {
                $latest[$p['student_id']] = $p;   // last decision per student wins
            }

            $promotedTotal = 0;
            $repeatedTotal = 0;
            $unplaced      = 0;
            foreach ($latest as $studentId => $p) {
                if ($p['next_class_name'] === 'Graduated') continue;

                $targetClassId = $this->resolveTargetClass($p, $targetYearId, $classMap);
                if ($targetClassId) {
                    DB::execute(
                        "UPDATE students SET academic_year_id = ?, current_class_id = ? WHERE id = ? AND status = 'active'",
                        [$targetYearId, $targetClassId, $studentId]
                    );
                    $p['promotion_status'] === 'promoted' ? $promotedTotal++ : $repeatedTotal++;
                } else {
                    // Never guess a class: move the year only and report for manual placement
                    DB::execute("UPDATE students SET academic_year_id = ? WHERE id = ? AND status = 'active'", [$targetYearId, $studentId]);
                    $unplaced++;
                }
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

            Audit::log('year_transition', 'students', null,
                ['source_year_id' => $sourceYearId],
                ['target_year_id' => $targetYearId, 'target_term_id' => $targetTermId,
                 'promoted' => $promotedTotal, 'repeated' => $repeatedTotal, 'unplaced' => $unplaced]);

            DB::commit();
            Session::updateActiveTerm();
            Notification::send(null, "Year Transition Complete", "Successfully transitioned to a new academic year. {$promotedTotal} students promoted.", 'success', '/admin/transition');
            $msg = "Year transition complete! {$promotedTotal} promoted and {$repeatedTotal} repeating students placed in their new classes.";
            if ($unplaced > 0) {
                $msg .= " {$unplaced} student(s) could not be matched to a class and need to be placed manually under Students.";
            }
            Session::flash($unplaced > 0 ? 'warning' : 'success', $msg);
        } catch (Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Year transition failed: ' . $e->getMessage());
        }
        $this->redirect();
    }

    /**
     * Find a student's class in the target year without guessing.
     * Order: class chosen at promotion → same class/section for repeaters →
     * exact name + section match. Returns null if there is no single clear match.
     */
    private function resolveTargetClass(array $p, int $targetYearId, array $classMap): ?int {
        if (!empty($p['target_class_id'])) {
            $inTarget = DB::queryValue(
                "SELECT id FROM classes WHERE id = ? AND academic_year_id = ?",
                [(int)$p['target_class_id'], $targetYearId]
            );
            if ($inTarget) return (int)$inTarget;
        }

        if ($p['promotion_status'] === 'repeated' && !empty($p['from_class_id']) && isset($classMap[$p['from_class_id']])) {
            return $classMap[$p['from_class_id']];
        }

        $name = trim((string)$p['next_class_name']);
        if ($name === '') return null;
        $section = (string)DB::queryValue("SELECT IFNULL(section, '') FROM classes WHERE id = ?", [(int)$p['from_class_id']]);

        // "BASIC 9 A" style names include the section; plain "BASIC 9" keeps the old section
        $matches = DB::query(
            "SELECT id FROM classes
             WHERE academic_year_id = ?
               AND (CONCAT(class_name, ' ', IFNULL(section, '')) = ?
                    OR (class_name = ? AND IFNULL(section, '') = ?))",
            [$targetYearId, $name, $name, $section]
        );
        return count($matches) === 1 ? (int)$matches[0]['id'] : null;
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
