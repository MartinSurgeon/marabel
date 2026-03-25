<?php
/**
 * Notification Controller
 * Handles notification management (read-all, view-all).
 */

class NotificationController {

    public function handle(): void {
        Session::requireAuth();

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $userId = Session::userId();

        // Robust routing check for /read-all suffix
        if (str_ends_with(rtrim(parse_url($uri, PHP_URL_PATH), '/'), '/read-all')) {
            $this->markAllRead($userId);
            return;
        }

        // Default view: View All Notifications
        $this->viewAll($userId);
    }

    private function markAllRead(int $userId): void {
        Notification::markAllAsRead($userId);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
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
        
        // Use generic dashboard/list template if no specific one exists
        // For now, we'll just use a direct include since we are in the controller
        $pageTitle = "All Notifications";
        include ROOT_PATH . '/templates/layout/header.php';
        ?>
        <div class="card bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="p-4 border-b flex justify-between items-center bg-gray-50">
                <h2 class="text-lg font-bold text-gray-800">System Activity & Notifications</h2>
                <?php if (!empty($notifications)): ?>
                    <form action="<?= defined('APP_BASE') ? APP_BASE : '' ?>/admin/notifications/read-all" method="POST">
                        <?= CSRF::field() ?>
                        <button type="submit" class="btn btn-primary btn-sm">Mark All as Read</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="divide-y divide-gray-100">
                <?php if (empty($notifications)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <p>No notifications found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                        <div class="p-4 hover:bg-gray-50 transition <?= $n['is_read'] ? 'opacity-70' : 'bg-purple-50/20' ?>">
                            <div class="flex items-start gap-4">
                                <div class="mt-1">
                                    <?php if ($n['type'] === 'success'): ?>
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    <?php elseif ($n['type'] === 'error'): ?>
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-600">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </span>
                                    <?php else: ?>
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <h4 class="font-bold text-gray-900"><?= htmlspecialchars($n['title']) ?></h4>
                                        <span class="text-xs text-gray-400"><?= date('M j, Y — g:i a', strtotime($n['created_at'])) ?></span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($n['message']) ?></p>
                                    <?php if ($n['link']): ?>
                                        <a href="<?= defined('APP_BASE') ? APP_BASE : '' ?><?= htmlspecialchars($n['link']) ?>" class="inline-block mt-2 text-xs font-semibold text-purple-600 hover:text-purple-800">
                                            Go to page &rarr;
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
        include ROOT_PATH . '/templates/layout/footer.php';
    }
}
