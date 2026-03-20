<?php
/**
 * SMS Centre Controller
 * Handles broadcast messaging, recipient filtering, and communication logs.
 */

class SMSController {

    public function handle(): void {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid request token.');
                $this->redirect();
            }
            $action = $_POST['_action'] ?? '';
            match ($action) {
                'send_broadcast' => $this->sendBroadcast(),
                default           => $this->redirect(),
            };
        }

        // Prepare data for view
        global $smsLogs, $classes, $totalSent, $recentLogs;

        $smsLogs = DB::query("SELECT * FROM sms_logs ORDER BY sent_at DESC LIMIT 100");
        $classes = DB::query("SELECT id, class_name, section FROM classes ORDER BY class_name");
        
        $totalSent = DB::queryOne("SELECT COUNT(*) as c FROM sms_logs WHERE status = 'sent'")['c'] ?? 0;
        $recentLogs= array_slice($smsLogs, 0, 5);
    }

    private function sendBroadcast(): void {
        $message = trim($_POST['message'] ?? '');
        $target  = $_POST['target'] ?? 'all'; // all, class_X
        $classId = 0;

        if (!$message) {
            Session::flash('error', 'Message content cannot be empty.');
            $this->redirect();
        }

        if (str_starts_with($target, 'class_')) {
            $classId = (int)substr($target, 6);
        }

        // Fetch recipient phone numbers (parents)
        $sql = "SELECT DISTINCT u.phone 
                FROM users u 
                JOIN student_parents sp ON sp.parent_user_id = u.id
                JOIN students s ON s.id = sp.student_id
                WHERE u.role = 'parent' AND u.is_active = 1 AND u.phone IS NOT NULL AND u.phone != ''";
        
        $params = [];
        if ($classId > 0) {
            $sql .= " AND s.current_class_id = ?";
            $params[] = $classId;
        }

        $recipients = DB::query($sql, $params);

        if (empty($recipients)) {
            Session::flash('error', 'No recipients found for the selected target.');
            $this->redirect();
        }

        $sentCount = 0;
        $failCount = 0;

        foreach ($recipients as $r) {
            $res = SMS::send($r['phone'], $message, 'broadcast');
            if ($res['success']) $sentCount++;
            else $failCount++;
        }

        if ($sentCount > 0) {
            Session::flash('success', "Message sent to {$sentCount} parent(s)." . ($failCount > 0 ? " ({$failCount} failed)" : ""));
        } else {
            Session::flash('error', "Failed to send messages. Check logs.");
        }

        $this->redirect();
    }

    private function redirect(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/admin/sms');
        exit;
    }
}
