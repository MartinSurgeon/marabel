<?php
/**
 * Result Publishing Controller — Hardened v2
 *
 * Improvements over v1:
 *  - Input validation before lock/publish (guards against empty class/term IDs)
 *  - Active-year fallback (uses latest year if none is marked active)
 *  - Batch-loaded scores via IN() to eliminate N+1 query pattern
 *  - Student count included in summary query
 *  - Defensive validation: blocks publish if class has no students or no subjects
 *  - Clear error messages surfaced back to the UI via Session::flash
 */

require_once __DIR__ . '/../Helpers/ResultCalculator.php';

class PublishController {

    public function handle(): void {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            $action = $_POST['_action'] ?? '';


            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid request token. Please try again.');
                $this->redirect();
            }

            match ($action) {
                'lock_class'    => $this->lockClass(),
                'unlock_class'  => $this->unlockClass(),
                'publish_class' => $this->publishClass(),
                'unpublish'     => $this->unpublishClass(),
                'bulk_unpublish'=> $this->bulkUnpublish(),
                default         => $this->redirect(),
            };
        }

        // ── Prepare view data ─────────────────────────────────────────
        global $activeTerm, $classesProgress, $activeYear;

        // Active year with fallback to the most recent year
        $activeYear = DB::queryOne("SELECT id, year_name FROM academic_years WHERE is_active = 1 LIMIT 1")
                   ?? DB::queryOne("SELECT id, year_name FROM academic_years ORDER BY year_name DESC LIMIT 1");

        // Active term with fallback to most recent term in the active year
        $activeTerm = DB::queryOne("SELECT * FROM terms WHERE is_active = 1 LIMIT 1");
        if (!$activeTerm && $activeYear) {
            $activeTerm = DB::queryOne(
                "SELECT * FROM terms WHERE academic_year_id = ? ORDER BY term_number DESC LIMIT 1",
                [$activeYear['id']]
            );
        }

        if (!$activeTerm || !$activeYear) {
            $classesProgress = [];
            return;
        }

        // Fetch classes and their scoring/student progress for the active term
        $classesProgress = DB::query(
            "SELECT
                c.id, c.class_name, c.section, sl.name AS level_name, sl.sort_order,
                rcl.is_published, rcl.published_at,
                -- Students enrolled in this class for this year
                (SELECT COUNT(*) FROM students s
                 WHERE s.current_class_id = c.id AND s.status = 'active' AND s.academic_year_id = ?) AS student_count,
                -- Subject registrations for this term
                (SELECT COUNT(*) FROM class_subjects cs
                 WHERE cs.class_id = c.id AND cs.term_id = ?) AS total_subjects,
                -- Locked subject registrations
                (SELECT COUNT(*) FROM class_subjects cs
                 WHERE cs.class_id = c.id AND cs.term_id = ? AND cs.is_locked = 1) AS locked_subjects,
                -- Students who have at least one score entered
                (SELECT COUNT(DISTINCT sba.student_id) FROM sba_component_scores sba
                 INNER JOIN class_subjects cs ON cs.id = sba.class_subject_id
                 WHERE cs.class_id = c.id AND sba.term_id = ?) AS students_with_scores
             FROM classes c
             JOIN school_levels sl ON sl.id = c.level_id
             LEFT JOIN report_card_locks rcl ON (rcl.class_id = c.id AND rcl.term_id = ?)
             WHERE c.academic_year_id = ?
             ORDER BY sl.sort_order, c.class_name",
            [
                $activeYear['id'],
                $activeTerm['id'],
                $activeTerm['id'],
                $activeTerm['id'],
                $activeTerm['id'],
                $activeYear['id'],
            ]
        );
    }

    // ── Lock ─────────────────────────────────────────────────────────
    private function lockClass(): void {
        [$classId, $termId] = $this->requireClassTerm();

        // Validate class and term exist
        if (!DB::queryOne("SELECT id FROM classes WHERE id = ?", [$classId])) {
            Session::flash('error', 'Class not found.');
            $this->redirect();
        }

        $locked = DB::execute(
            "UPDATE class_subjects SET is_locked = 1, locked_at = CURRENT_TIMESTAMP
             WHERE class_id = ? AND term_id = ? AND is_locked = 0",
            [$classId, $termId]
        );

        Session::flash('success', "Score entry is now locked for this class. Teachers cannot modify scores.");
        $this->redirect();
    }

    // ── Unlock ───────────────────────────────────────────────────────
    private function unlockClass(): void {
        [$classId, $termId] = $this->requireClassTerm();

        DB::execute(
            "UPDATE class_subjects SET is_locked = 0, locked_at = NULL
             WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );

        // Remove published flag — must re-publish after unlocking
        DB::execute(
            "UPDATE report_card_locks SET is_published = 0 WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );

        Session::flash('info', "Scores unlocked. Remember to re-publish after any changes are made.");
        $this->redirect();
    }

    // ── Publish ──────────────────────────────────────────────────────
    private function publishClass(): void {
        [$classId, $termId] = $this->requireClassTerm();

        // Pre-flight checks
        $studentCount = DB::queryOne(
            "SELECT COUNT(*) as cnt FROM students WHERE current_class_id = ? AND status = 'active'",
            [$classId]
        )['cnt'] ?? 0;

        $subjectCount = DB::queryOne(
            "SELECT COUNT(*) as cnt FROM class_subjects WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        )['cnt'] ?? 0;

        if ((int)$studentCount === 0) {
            Session::flash('error', 'Cannot publish: no active students are enrolled in this class.');
            $this->redirect();
        }

        if ((int)$subjectCount === 0) {
            Session::flash('error', 'Cannot publish: no subjects have been assigned to this class for the active term. Assign subjects in Teacher Management first.');
            $this->redirect();
        }

        try {
            DB::beginTransaction();

            ResultCalculator::compute($classId, $termId);

            DB::execute(
                "INSERT INTO report_card_locks (class_id, term_id, is_published, published_at, published_by)
                 VALUES (?, ?, 1, CURRENT_TIMESTAMP, ?)
                 ON DUPLICATE KEY UPDATE
                    is_published = 1, published_at = CURRENT_TIMESTAMP, published_by = ?",
                [$classId, $termId, Session::get('user_id'), Session::get('user_id')]
            );

            DB::commit();
            $className = DB::queryValue("SELECT class_name FROM classes WHERE id = ?", [$classId]);
            
            require_once __DIR__ . '/../Helpers/Notification.php';
            // 1. Notify Admins (Global is okay for system update)
            Notification::send(null, "Results Published", "Report cards for {$className} have been published.", 'success', '/admin/publish');
            
            // 2. Notify Parents (Primary Contact)
            $parents = DB::query("
                SELECT DISTINCT u.phone, s.full_name, u.id as parent_user_id
                FROM users u
                JOIN student_parents sp ON u.id = sp.parent_user_id
                JOIN students s ON sp.student_id = s.id
                WHERE s.current_class_id = ? AND s.status = 'active' AND sp.is_primary = 1
            ", [$classId]);

            $sendSms = (isset($_POST['send_sms']) && $_POST['send_sms'] === '1');
            $termName = $term['name'] ?? 'Current Term';
            $yearName = $activeYear['year_name'] ?? '';

            foreach ($parents as $p) {
                // In-app notification
                Notification::send($p['parent_user_id'], "Results Released", "Academic results for {$p['full_name']} ({$className}) are available for viewing.", 'success', '/parent');
                
                // Optional SMS notification
                if ($sendSms && !empty($p['phone'])) {
                    $smsMsg = "Dear Parent, results for {$p['full_name']} ({$termName} {$yearName}) have been published. View now at the portal.";
                    SMS::send($p['phone'], $smsMsg, 'report_card');
                }
            }
            
            /* 
             * Student notifications are disabled for now as students do not have 
             * linked 'user_id' records in the students table yet.
             * 
             * // 3. Notify Students in this class
             * $studentsUsers = DB::query("
             *    SELECT user_id FROM students WHERE current_class_id = ? AND status = 'active' AND user_id IS NOT NULL
             * ", [$classId]);
             * foreach ($studentsUsers as $s) {
             *    Notification::send($s['user_id'], "Report Card Released", "Your results for the current term have been published.", 'success', '/student');
             * }
             */
            Session::flash('success', "Results published! Parents and students can now view report cards.");
        } catch (\Throwable $e) {
            if (DB::inTransaction()) DB::rollBack();
            Session::flash('error', "Publishing failed: " . $e->getMessage());
        }

        $this->redirect();
    }

    // ── Unpublish ────────────────────────────────────────────────────
    private function unpublishClass(): void {
        [$classId, $termId] = $this->requireClassTerm();

        DB::execute(
            "UPDATE report_card_locks SET is_published = 0 WHERE class_id = ? AND term_id = ?",
            [$classId, $termId]
        );

        $className = DB::queryValue("SELECT class_name FROM classes WHERE id = ?", [$classId]);
        Notification::send(null, "Results Unpublished", "Report cards for {$className} have been hidden.", 'info', '/admin/publish');
        Session::flash('info', "Reports hidden. They are no longer visible to parents or students.");
        $this->redirect();
    }

    private function bulkUnpublish(): void {
        $termId = (int)($_POST['term_id'] ?? 0);
        if (!$termId) {
            Session::flash('error', 'Invalid term.');
            $this->redirect();
        }

        DB::execute(
            "UPDATE report_card_locks SET is_published = 0 WHERE term_id = ?",
            [$termId]
        );

        Notification::send(null, "Bulk Results Hidden", "All results for the term have been unpublished.", 'warning', '/admin/publish');
        Session::flash('success', "All results for this term have been unpublished and hidden from parents/students.");
        $this->redirect();
    }


    private function requireClassTerm(): array {
        $classId = (int)($_POST['class_id'] ?? 0);
        $termId  = (int)($_POST['term_id']  ?? 0);

        if (!$classId || !$termId) {
            Session::flash('error', 'Invalid request: missing class or term.');
            $this->redirect();
        }

        return [$classId, $termId];
    }

    private function redirect(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/admin/publish');
        exit;
    }
}
