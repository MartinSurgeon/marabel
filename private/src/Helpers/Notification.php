<?php
/**
 * Notification Helper
 * Uaddara Basic School — SBA Management System
 */

class Notification {

    /**
     * Send notification to a specific user
     */
    public static function send(?int $userId, string $title, string $message, string $type = 'info', ?string $link = null): bool {
        return (bool)DB::insert(
            "INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)",
            [$userId, $title, $message, $type, $link]
        );
    }

    /**
     * Send notification to all users with a specific role
     */
    public static function sendToRole(string $role, string $title, string $message, string $type = 'info', ?string $link = null): void {
        $users = DB::query("SELECT id FROM users WHERE role = ? AND is_active = 1", [$role]);
        foreach ($users as $user) {
            self::send($user['id'], $title, $message, $type, $link);
        }
    }

    /**
     * Get unread count for a user
     */
    public static function getUnreadCount(int $userId): int {
        return (int)DB::queryValue(
            "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0",
            [$userId]
        );
    }

    /**
     * Get latest notifications for a user
     */
    public static function getLatest(int $userId, int $limit = 5): array {
        return DB::query(
            "SELECT * FROM notifications 
             WHERE (user_id = ? OR user_id IS NULL) 
             ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    /**
     * Mark a single notification as read
     */
    public static function markAsRead(int $notificationId, int $userId): void {
        DB::execute(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = ? OR user_id IS NULL)",
            [$notificationId, $userId]
        );
    }

    /**
     * Mark all as read for a user
     */
    public static function markAllAsRead(int $userId): void {
        DB::execute(
            "UPDATE notifications SET is_read = 1 WHERE (user_id = ? OR user_id IS NULL)",
            [$userId]
        );
    }
}
