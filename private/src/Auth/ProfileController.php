<?php
/**
 * Profile Controller
 * Handles user profile actions (e.g., Change Password)
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';
require_once PRIVATE_PATH . '/src/Helpers/Validator.php';

class ProfileController {

    public function handle(): void {
        Session::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::verify()) {
                Session::flash('error', 'Invalid security token. Please try again.');
                return;
            }
            $this->updatePassword();
        }
    }

    private function updatePassword(): void {
        $userId = Session::userId();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // 1. Basic validation
        if (strlen($newPassword) < 8) {
            Session::flash('error', 'New password must be at least 8 characters long.');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'Passwords do not match.');
            return;
        }

        // 2. Verify current password
        $user = DB::queryOne("SELECT password_hash FROM users WHERE id = ?", [$userId]);
        
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            Session::flash('error', 'The current password you entered is incorrect.');
            return;
        }

        // 3. Update password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        DB::execute("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $userId]);

        Session::flash('success', 'Your password has been changed successfully.');
        
        // Redirect based on role
        $base = defined('APP_BASE') ? APP_BASE : '';
        header('Location: ' . $base . '/' . Session::role());
        exit;
    }
}
