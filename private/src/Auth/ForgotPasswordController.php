<?php
/**
 * Forgot Password Controller
 * Uaddara Basic School — SBA Management System
 *
 * Flow (staff / admin / teacher only):
 *   Step 1  GET  /forgot-password          → show email form
 *           POST /forgot-password          → validate email, send OTP, redirect to /forgot-password/verify
 *   Step 2  GET  /forgot-password/verify   → show OTP form (requires fp_email in session)
 *           POST /forgot-password/verify   → verify OTP, set fp_verified flag, redirect to /forgot-password/reset
 *   Step 3  GET  /forgot-password/reset    → show new-password form (requires fp_verified in session)
 *           POST /forgot-password/reset    → update password, clear session state, redirect to /login
 *
 * Security notes:
 *  - Reuses the existing `otp_tokens` table (keyed on phone).
 *  - OTP expiry: SMS_OTP_EXPIRY_MINUTES constant (default 10 min).
 *  - Generic error messages prevent email enumeration.
 *  - Rate-limited via existing login_attempts table (same identifier = email).
 *  - Verification flag is stored in PHP session (server-side), not a URL token.
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';
require_once PRIVATE_PATH . '/src/Helpers/CSRF.php';
require_once PRIVATE_PATH . '/src/Helpers/SMS.php';
require_once PRIVATE_PATH . '/src/Helpers/Notification.php';

class ForgotPasswordController {

    public function handle(): void {
        $rawPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $appBase = defined('APP_BASE') ? APP_BASE : '';
        $path    = '/' . ltrim(substr($rawPath, strlen($appBase)), '/');

        $method = $_SERVER['REQUEST_METHOD'];

        // Route to the correct step handler
        match (true) {
            // ── Step 1: Email form ───────────────────────────
            $path === '/forgot-password' && $method === 'POST'
                => $this->requestOtp(),

            // ── Step 2: Verify OTP ───────────────────────────
            str_starts_with($path, '/forgot-password/verify') && $method === 'POST'
                => $this->verifyOtp(),

            // ── Step 3: Reset password ───────────────────────
            str_starts_with($path, '/forgot-password/reset') && $method === 'POST'
                => $this->resetPassword(),

            // GET requests fall through — templates handle display
            default => null,
        };
    }

    // ── Step 1: Validate email and dispatch OTP ────────────────
    private function requestOtp(): void {
        if (!CSRF::verify()) {
            Session::flash('fp_error', 'Invalid request. Please try again.');
            $this->redirect('/forgot-password');
        }

        $email = trim(strtolower($_POST['email'] ?? ''));

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('fp_error', 'Please enter a valid email address.');
            $this->redirect('/forgot-password');
        }

        // Rate-limit check (reuse login_attempts table, identifier = email)
        if ($this->isLockedOut($email)) {
            Session::flash('fp_error', 'Too many attempts. Please wait 15 minutes before trying again.');
            $this->redirect('/forgot-password');
        }

        // Look up active staff user — intentionally returns same message whether found or not
        $user = DB::queryOne(
            "SELECT id, full_name, phone FROM users
             WHERE email = ? AND role IN ('admin','teacher') AND is_active = 1",
            [$email]
        );

        // Always show the same generic message to prevent email enumeration
        $generic = 'If this email is registered to a staff account, a verification code has been sent to the associated phone number.';

        if (!$user || empty($user['phone'])) {
            // Record a failed attempt to throttle abusers, but don't reveal the reason
            $this->recordFailedAttempt($email);
            Session::flash('fp_info', $generic);
            $this->redirect('/forgot-password');
        }

        // Generate & store OTP
        $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hash    = hash('sha256', $otp);
        $expMins = defined('SMS_OTP_EXPIRY_MINUTES') ? SMS_OTP_EXPIRY_MINUTES : 10;
        $expires = date('Y-m-d H:i:s', time() + ($expMins * 60));

        // Invalidate old reset tokens for this phone to prevent replay
        DB::execute("DELETE FROM otp_tokens WHERE phone = ?", [$user['phone']]);

        DB::insert(
            "INSERT INTO otp_tokens (phone, token_hash, expires_at) VALUES (?, ?, ?)",
            [$user['phone'], $hash, $expires]
        );

        // Send SMS
        $schoolName = Config::get('school_name', 'Uaddara Basic School');
        try {
            SMS::send(
                $user['phone'],
                "Your {$schoolName} password reset code is: {$otp}. Valid for {$expMins} minutes. Do not share this code."
            );
        } catch (\Throwable $e) {
            error_log("ForgotPassword SMS failed for user #{$user['id']}: " . $e->getMessage());
        }

        // Store minimal state in session (no user_id — don't confirm identity yet)
        Session::set('fp_email',        $email);
        Session::set('fp_phone',        $user['phone']); // full phone, server-side only
        Session::set('fp_masked_phone', $this->maskPhone($user['phone']));
        Session::set('fp_otp_sent_at',  time());

        Session::flash('fp_info', $generic);
        $this->redirect('/forgot-password/verify');
    }

    // ── Step 2: Verify OTP ─────────────────────────────────────
    private function verifyOtp(): void {
        if (!CSRF::verify()) {
            Session::flash('fp_otp_error', 'Invalid request. Please try again.');
            $this->redirect('/forgot-password/verify');
        }

        $email = Session::get('fp_email');
        $phone = Session::get('fp_phone');
        $otp   = trim($_POST['otp'] ?? '');

        // Guard: must have a pending reset
        if (!$email || !$phone) {
            $this->redirect('/forgot-password');
        }

        if (!$otp || strlen($otp) !== 6 || !ctype_digit($otp)) {
            Session::flash('fp_otp_error', 'Please enter the full 6-digit code.');
            $this->redirect('/forgot-password/verify');
        }

        // Rate-limit (same identifier as step 1)
        if ($this->isLockedOut($email)) {
            Session::flash('fp_error', 'Too many failed attempts. Please wait 15 minutes.');
            $this->clearFpSession();
            $this->redirect('/forgot-password');
        }

        $hash = hash('sha256', $otp);
        $now  = date('Y-m-d H:i:s');

        $record = DB::queryOne(
            "SELECT id FROM otp_tokens
             WHERE phone = ? AND token_hash = ? AND expires_at > ? AND used_at IS NULL",
            [$phone, $hash, $now]
        );

        if (!$record) {
            $this->recordFailedAttempt($email);
            Session::flash('fp_otp_error', 'Invalid or expired code. Please check and try again.');
            $this->redirect('/forgot-password/verify');
        }

        // Mark token as used immediately so it can't be replayed
        DB::execute("UPDATE otp_tokens SET used_at = NOW() WHERE id = ?", [$record['id']]);

        // Confirm user identity now that OTP is verified
        $user = DB::queryOne(
            "SELECT id FROM users WHERE email = ? AND role IN ('admin','teacher') AND is_active = 1",
            [$email]
        );

        if (!$user) {
            // Extremely unlikely, but handle it
            Session::flash('fp_error', 'Account not found. Please contact the administrator.');
            $this->clearFpSession();
            $this->redirect('/forgot-password');
        }

        // Promote session to verified state
        $this->clearAttempts($email);
        Session::set('fp_verified',  true);
        Session::set('fp_user_id',   $user['id']);
        // Clear the OTP-related keys (no longer needed)
        Session::delete('fp_phone');
        Session::delete('fp_otp_sent_at');

        $this->redirect('/forgot-password/reset');
    }

    // ── Step 3: Set new password ────────────────────────────────
    private function resetPassword(): void {
        if (!CSRF::verify()) {
            Session::flash('fp_reset_error', 'Invalid request. Please try again.');
            $this->redirect('/forgot-password/reset');
        }

        $userId      = Session::get('fp_user_id');
        $verified    = Session::get('fp_verified');
        $newPassword = $_POST['new_password']     ?? '';
        $confirm     = $_POST['confirm_password'] ?? '';

        // Must be verified
        if (!$verified || !$userId) {
            $this->redirect('/forgot-password');
        }

        // Validate
        if (strlen($newPassword) < 8) {
            Session::flash('fp_reset_error', 'Password must be at least 8 characters long.');
            $this->redirect('/forgot-password/reset');
        }

        if ($newPassword !== $confirm) {
            Session::flash('fp_reset_error', 'Passwords do not match. Please try again.');
            $this->redirect('/forgot-password/reset');
        }

        // Update the password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => defined('BCRYPT_COST') ? BCRYPT_COST : 12]);
        DB::execute("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $userId]);

        // Fetch user name for the notification
        $user = DB::queryOne("SELECT full_name FROM users WHERE id = ?", [$userId]);
        $name = $user['full_name'] ?? 'your account';

        // Notify the user that their password was changed
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        Notification::send(
            $userId,
            'Password Reset Successful',
            "Your password for {$name} was reset via the forgot-password flow on " . date('d M Y \a\t H:i') . ". If this wasn't you, contact the administrator immediately.",
            'warning',
            '/profile/password'
        );

        // Clean up all forgot-password session state
        $this->clearFpSession();

        // Redirect to login with a success message
        Session::flash('login_error', ''); // clear any lingering error
        Session::flash('fp_success',  'Password reset successfully! Please sign in with your new password.');
        $this->redirect('/login?reset=1');
    }

    // ── Helpers ────────────────────────────────────────────────

    /**
     * Mask a phone number: 0557869989 → 055****989
     */
    private function maskPhone(string $phone): string {
        $clean = preg_replace('/\D/', '', $phone);
        if (strlen($clean) < 6) return '•••••';
        $prefix = substr($clean, 0, 3);
        $suffix = substr($clean, -3);
        $masked = str_repeat('*', strlen($clean) - 6);
        return $prefix . $masked . $suffix;
    }

    private function isLockedOut(string $identifier): bool {
        $cutoff   = date('Y-m-d H:i:s', strtotime('-' . LOGIN_LOCKOUT_MINS . ' minutes'));
        $attempts = DB::queryOne(
            "SELECT COUNT(*) as cnt FROM login_attempts WHERE identifier = ? AND attempted_at > ?",
            [$identifier, $cutoff]
        );
        return ($attempts['cnt'] ?? 0) >= LOGIN_MAX_ATTEMPTS;
    }

    private function recordFailedAttempt(string $identifier): void {
        DB::execute(
            "INSERT INTO login_attempts (identifier, ip_address) VALUES (?, ?)",
            [$identifier, $_SERVER['REMOTE_ADDR'] ?? '']
        );
    }

    private function clearAttempts(string $identifier): void {
        DB::execute("DELETE FROM login_attempts WHERE identifier = ?", [$identifier]);
    }

    private function clearFpSession(): void {
        Session::delete('fp_email');
        Session::delete('fp_phone');
        Session::delete('fp_masked_phone');
        Session::delete('fp_otp_sent_at');
        Session::delete('fp_verified');
        Session::delete('fp_user_id');
    }

    private function redirect(string $path): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header("Location: {$base}{$path}");
        exit;
    }
}
