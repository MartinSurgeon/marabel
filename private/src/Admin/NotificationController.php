<?php
/**
 * Notification Controller
 * Handles notification management (read single, read all, view all).
 * Follows HCI principles: clear individual feedback, progressive disclosure, no cognitive overload.
 */

class NotificationController {

    public function handle(): void {
        Session::requireAuth();

        $uriPath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        $userId  = Session::userId();

        if (str_ends_with($uriPath, '/read-all')) {
            $this->markAllRead($userId);
            return;
        }

        if (str_ends_with($uriPath, '/read')) {
            $this->markOneRead($userId);
            return;
        }

        // Default view: View All Notifications
        $this->viewAll($userId);
    }

    private function markOneRead(int $userId): void {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id > 0) {
            Notification::markAsRead($id, $userId);
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'unreadCount' => Notification::getUnreadCount($userId)
            ]);
            exit;
        }

        $base = defined('APP_BASE') ? APP_BASE : '';
        header("Location: {$base}/admin/notifications");
        exit;
    }

    private function markAllRead(int $userId): void {
        Notification::markAllAsRead($userId);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'unreadCount' => 0]);
            exit;
        }
        Session::flash('success', 'All notifications marked as read.');
        $base = defined('APP_BASE') ? APP_BASE : '';
        header("Location: {$base}/admin/notifications");
        exit;
    }

    private function viewAll(int $userId): void {
        global $notifications;
        $notifications = DB::query(
            "SELECT * FROM notifications 
             WHERE (user_id = ? OR user_id IS NULL) 
             ORDER BY created_at DESC",
            [$userId]
        );
        
        $base = defined('APP_BASE') ? APP_BASE : '';
        $pageTitle = "All Notifications";
        include ROOT_PATH . '/templates/layout/header.php';
        ?>
        <div class="card bg-white shadow-sm rounded-lg overflow-hidden mb-6">
            <div class="p-4 border-b flex justify-between items-center bg-gray-50 flex-wrap gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 m-0">Activity & Notifications</h2>
                    <p class="text-xs text-gray-500 m-0">Manage and review your personal and system updates</p>
                </div>
                <?php if (!empty($notifications)): ?>
                    <form action="<?= $base ?>/admin/notifications/read-all" method="POST" class="m-0">
                        <?= CSRF::field() ?>
                        <button type="submit" class="btn btn-secondary btn-sm" id="btn-mark-all">Mark All as Read</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="divide-y divide-gray-100" id="notif-full-list">
                <?php if (empty($notifications)): ?>
                    <div class="p-12 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <p class="font-medium">You're all caught up!</p>
                        <p class="text-xs text-gray-400 mt-1">No pending notifications.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                        <div class="p-4 hover:bg-gray-50 transition flex items-start gap-4 <?= $n['is_read'] ? 'opacity-60 bg-white' : 'bg-purple-50/25 font-semibold' ?>" id="notif-item-<?= $n['id'] ?>">
                            <div class="mt-1 flex-shrink-0">
                                <?php if ($n['type'] === 'success'): ?>
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                <?php elseif ($n['type'] === 'error'): ?>
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-600">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                <?php else: ?>
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100 text-purple-600">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-2">
                                    <h4 class="font-bold text-gray-900 m-0 text-sm"><?= htmlspecialchars($n['title']) ?></h4>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400 whitespace-nowrap"><?= date('M j, Y — g:i a', strtotime($n['created_at'])) ?></span>
                                        <?php if (!$n['is_read']): ?>
                                            <button 
                                                class="btn btn-ghost btn-xs text-purple-600 hover:text-purple-800 font-medium py-1 px-2 text-[11px]" 
                                                onclick="markSingleRead(<?= $n['id'] ?>, this)"
                                                title="Mark this notification as read"
                                            >
                                                Mark read
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mt-1 mb-0 font-normal"><?= htmlspecialchars($n['message']) ?></p>
                                <?php if ($n['link']): ?>
                                    <a href="<?= $base ?><?= htmlspecialchars($n['link']) ?>" class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-purple-600 hover:text-purple-800">
                                        View details &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <script>
        function markSingleRead(notifId, btn) {
            btn.disabled = true;
            btn.textContent = '...';
            const item = document.getElementById('notif-item-' + notifId);
            
            fetch('<?= $base ?>/admin/notifications/read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'id=' + encodeURIComponent(notifId)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.remove();
                    if (item) {
                        item.classList.remove('bg-purple-50/25', 'font-semibold');
                        item.classList.add('opacity-60', 'bg-white');
                    }
                    const badge = document.querySelector('#notif-toggle .badge');
                    if (badge && data.unreadCount <= 0) badge.remove();
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.textContent = 'Mark read';
            });
        }
        </script>
        <?php
        include ROOT_PATH . '/templates/layout/footer.php';
    }
}
