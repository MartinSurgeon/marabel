<?php
/**
 * SMS Centre Controller
 * Handles broadcast messaging, recipient filtering, and communication logs.
 */
require_once PRIVATE_PATH . '/src/Helpers/Config.php';
require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/CSRF.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';

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
                'update_settings' => $this->updateSettings(),
                'delete_logs'     => $this->deleteLogs(),
                'check_delivery'  => $this->checkDelivery(),
                default          => $this->redirect(),
            };
        }

        // Prepare data for view
        global $smsLogs, $classes, $totalSent, $recentLogs, $smsBalance, $pagination, $smsStats;

        // Pagination setup
        $limit = 20;
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $offset= ($page - 1) * $limit;

        $totalLogsCount = DB::queryValue("SELECT COUNT(*) FROM sms_logs") ?? 0;
        $totalPages     = ceil($totalLogsCount / $limit);

        $smsLogs = DB::query("SELECT * FROM sms_logs ORDER BY sent_at DESC LIMIT ? OFFSET ?", [$limit, $offset]);
        $classes = DB::query("SELECT id, class_name, section FROM classes ORDER BY class_name");
        
        $smsStats = ['delivered' => 0, 'pending' => 0, 'failed' => 0];
        foreach (DB::query("SELECT status, COUNT(*) AS c FROM sms_logs GROUP BY status") as $row) {
            $key = match ($row['status']) {
                'delivered'                                    => 'delivered',
                'pending'                                      => 'pending',
                'undelivered', 'expired', 'rejected', 'failed' => 'failed',
                default                                        => null,   // 'sent': old rows, never tracked
            };
            if ($key) $smsStats[$key] += (int)$row['c'];
        }
        $totalSent = $smsStats['delivered'];
        $recentLogs= array_slice($smsLogs, 0, 5);

        // Fetch real-time balance
        $smsBalance = SMS::getBalance();
        
        $pagination = [
            'current' => $page,
            'total'   => $totalPages,
            'count'   => $totalLogsCount,
            'limit'   => $limit
        ];
    }

    private function deleteLogs(): void {
        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids)) $ids = [$ids];
        $ids = array_map('intval', array_filter($ids));

        if (empty($ids)) {
            Session::flash('error', 'No logs selected for deletion.');
        } else {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            DB::execute("DELETE FROM sms_logs WHERE id IN ($placeholders)", $ids);
            Session::flash('success', count($ids) . ' log(s) deleted successfully.');
        }
        $this->redirect();
    }

    /**
     * Ask the gateway for the delivery status of messages still waiting.
     * Least-recently-checked first, so repeated clicks work through a backlog.
     */
    private function checkDelivery(): void {
        try {
            $rows = DB::query(
                "SELECT id, batch_id, message_id FROM sms_logs
                 WHERE status = 'pending' AND batch_id IS NOT NULL
                   AND sent_at >= NOW() - INTERVAL 14 DAY
                 ORDER BY status_checked_at ASC, id DESC
                 LIMIT 30"
            );
        } catch (\PDOException $e) {
            Session::flash('error', 'Delivery tracking is not set up on this database yet. Run private/database/sms_delivery_tracking.sql in phpMyAdmin first.');
            $this->redirect();
        }

        if (empty($rows)) {
            Session::flash('info', 'No messages are waiting for a delivery report.');
            $this->redirect();
        }

        $counts = ['delivered' => 0, 'pending' => 0, 'failed' => 0];
        $errors = 0;
        $lastError = '';

        foreach ($rows as $row) {
            $res  = SMS::checkDelivery($row['batch_id']);
            $code = null;
            if ($res['success']) {
                foreach ($res['destinations'] as $d) {
                    if ($row['message_id'] && $d['message_id'] === $row['message_id']) { $code = $d['status_code']; break; }
                }
                if ($code === null && count($res['destinations']) === 1) $code = $res['destinations'][0]['status_code'];
            } else {
                $errors++;
                $lastError = $res['message'];
            }

            if ($code) {
                $status = SMS::statusBucket($code);
                DB::execute(
                    "UPDATE sms_logs SET status = ?, status_code = ?, status_checked_at = NOW() WHERE id = ?",
                    [$status, $code, $row['id']]
                );
                $counts[in_array($status, ['delivered', 'pending'], true) ? $status : 'failed']++;
            } else {
                DB::execute("UPDATE sms_logs SET status_checked_at = NOW() WHERE id = ?", [$row['id']]);
                if ($res['success']) $counts['pending']++;
            }
        }

        $msg = "Checked " . count($rows) . " message(s): {$counts['delivered']} delivered, "
             . "{$counts['pending']} still waiting, {$counts['failed']} not delivered.";
        if ($errors > 0) {
            $msg .= " {$errors} could not be checked ({$lastError}).";
        }
        Session::flash($counts['failed'] > 0 || $errors > 0 ? 'warning' : 'success', $msg);
        $this->redirect();
    }

    private function updateSettings(): void {
        $apiKey = $_POST['sms_api_key'] ?? '';
        $sender = $_POST['sms_sender'] ?? 'Marabel';
        $host   = $_POST['sms_host'] ?? 'api.smsonlinegh.com';

        Config::set('sms_api_key', $apiKey, 'connectivity');
        Config::set('sms_sender', $sender, 'connectivity');
        Config::set('sms_host', $host, 'connectivity');

        Session::flash('success', 'SMS connectivity settings updated.');
        $this->redirect();
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
            Session::flash('success', "Message accepted for {$sentCount} parent(s). Use \"Check delivery\" below to see who received it."
                . ($failCount > 0 ? " ({$failCount} rejected: see the history for the reason)" : ""));
        } else {
            Session::flash('error', "No message was accepted. See the history below for the reason.");
        }

        $this->redirect();
    }

    private function redirect(): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/admin/sms');
        exit;
    }
}
