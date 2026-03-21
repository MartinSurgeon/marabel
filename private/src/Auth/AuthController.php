<?php
/**
 * Authentication Controller
 * Uaddara Basic School — SBA Management System
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';
require_once PRIVATE_PATH . '/src/Helpers/CSRF.php';

class AuthController {

    public function handle(): void {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ($path === '/logout') {
            $this->logout();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        if (!CSRF::verify()) {
            Session::flash('login_error', 'Invalid request. Please try again.');
            $this->redirect('/login');
        }

        $roleType = $_POST['role_type'] ?? '';

        match ($roleType) {
            'staff'          => $this->staffLogin(),
            'student'        => $this->studentLogin(),
            'parent'         => $this->parentRequestOtp(),
            'parent_verify'  => $this->parentVerifyOtp(),
            default          => $this->redirect('/login'),
        };
    }

    private function staffLogin(): void {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            Session::flash('login_error', 'Email and password are required.');
            $this->redirect('/login');
        }

        // Rate limit check
        if ($this->isLockedOut($email)) {
            Session::flash('login_error', 'Too many failed attempts. Please wait 15 minutes.');
            $this->redirect('/login');
        }

        $user = DB::queryOne(
            "SELECT * FROM users WHERE email = ? AND role IN ('admin','teacher') AND is_active = 1",
            [$email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->recordFailedAttempt($email);
            Session::flash('login_error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        // Success
        $this->clearAttempts($email);
        $this->createSession($user);
        $this->updateLastLogin($user['id']);

        $this->redirect($user['role'] === 'admin' ? '/admin' : '/teacher');
    }

    private function studentLogin(): void {
        $studentId = trim($_POST['student_id'] ?? '');
        $pin       = $_POST['pin'] ?? '';

        if (!$studentId || !$pin || strlen($pin) !== 4) {
            Session::flash('login_error', 'Student ID and 4-digit PIN are required.');
            $this->redirect('/login');
        }

        if ($this->isLockedOut($studentId)) {
            Session::flash('login_error', 'Too many failed attempts. Please wait 15 minutes.');
            $this->redirect('/login');
        }

        $student = DB::queryOne(
            "SELECT s.*, u.id as user_id FROM students s
             JOIN users u ON u.role = 'student' AND u.id = s.id
             WHERE s.student_id_number = ? AND s.status = 'active'",
            [$studentId]
        );

        if (!$student || !password_verify($pin, $student['pin_hash'])) {
            $this->recordFailedAttempt($studentId);
            Session::flash('login_error', 'Invalid Student ID or PIN.');
            $this->redirect('/login');
        }

        $this->clearAttempts($studentId);

        // Create a pseudo-user session for the student
        Session::regenerate();
        Session::set('user_id',   $student['id']);
        Session::set('user_name', $student['full_name']);
        Session::set('user_role', ROLE_STUDENT);
        Session::set('student_id', $student['id']);

        $this->redirect('/student');
    }

    // ── Parent OTP Request ─────────────────────────────────────
    private function parentRequestOtp(): void {
        $phone = trim($_POST['phone'] ?? '');

        if (!$phone) {
            Session::flash('login_error', 'Please enter your registered phone number.');
            $this->redirect('/login');
        }

        if ($this->isLockedOut($phone)) {
            Session::flash('login_error', 'Too many attempts. Please wait 15 minutes.');
            $this->redirect('/login');
        }

        // Check that this phone is registered in users as a parent
        $parent = DB::queryOne(
            "SELECT id, full_name FROM users WHERE phone = ? AND role = 'parent' AND is_active = 1",
            [$phone]
        );

        if (!$parent) {
            // Don't reveal whether phone exists; show generic message
            Session::flash('otp_info', 'If this number is registered, a code has been sent.');
            Session::set('otp_phone', $phone); // needed for template display
            $this->redirect('/otp');
        }

        // Generate 6-digit OTP
        $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hash    = hash('sha256', $otp);
        $expires = date('Y-m-d H:i:s', time() + 300); // 5 minutes

        // Invalidate old tokens for this phone
        DB::execute("DELETE FROM otp_tokens WHERE phone = ?", [$phone]);

        // Store new token
        DB::insert(
            "INSERT INTO otp_tokens (phone, token_hash, expires_at) VALUES (?, ?, ?)",
            [$phone, $hash, $expires]
        );

        // Send SMS (graceful if SMS service unavailable)
        try {
            $sms = new SMS();
            $sms->send($phone, "Your Uaddara Basic School login code is: {$otp}. Valid for 5 minutes. Do not share.");
        } catch (\Throwable $e) {
            // Log but don't expose error to user; also store OTP in session for dev/testing
            error_log("OTP SMS failed: " . $e->getMessage());
        }

        // Store phone in session for the OTP page
        Session::set('otp_phone', $phone);
        Session::set('otp_parent_id', $parent['id']);

        Session::flash('otp_info', "A 6-digit code has been sent to {$phone}.");
        $this->redirect('/otp');
    }

    // ── Parent OTP Verify ─────────────────────────────────────
    private function parentVerifyOtp(): void {
        $phone    = Session::get('otp_phone');
        $parentId = Session::get('otp_parent_id');
        $otp      = trim($_POST['otp'] ?? '');

        if (!$phone || !$otp || strlen($otp) !== 6) {
            Session::flash('otp_error', 'Invalid code. Please try again.');
            $this->redirect('/otp');
        }

        if ($this->isLockedOut($phone)) {
            Session::flash('login_error', 'Too many failed attempts. Please wait 15 minutes.');
            $this->redirect('/login');
        }

        $hash   = hash('sha256', $otp);
        $now    = date('Y-m-d H:i:s');
        $record = DB::queryOne(
            "SELECT id FROM otp_tokens
             WHERE phone = ? AND token_hash = ? AND expires_at > ? AND used_at IS NULL",
            [$phone, $hash, $now]
        );

        if (!$record) {
            $this->recordFailedAttempt($phone);
            Session::flash('otp_error', 'Invalid or expired code. Please try again.');
            $this->redirect('/otp');
        }

        // Mark token as used
        DB::execute("UPDATE otp_tokens SET used_at = NOW() WHERE id = ?", [$record['id']]);

        // Load parent user
        $parent = DB::queryOne(
            "SELECT id, full_name, phone FROM users WHERE id = ? AND role = 'parent'",
            [$parentId]
        );

        if (!$parent) {
            Session::flash('otp_error', 'Account not found. Please contact the school.');
            $this->redirect('/login');
        }

        // Create session
        $this->clearAttempts($phone);
        Session::regenerate();
        Session::set('user_id',   $parent['id']);
        Session::set('user_name', $parent['full_name']);
        Session::set('user_role', 'parent');
        Session::delete('otp_phone');
        Session::delete('otp_parent_id');

        $this->redirect('/parent');
    }

    private function logout(): void {
        Session::destroy();
        $this->redirect('/login');
    }

    private function createSession(array $user): void {
        Session::regenerate();
        Session::set('user_id',   $user['id']);
        Session::set('user_name', $user['full_name']);
        Session::set('user_role', $user['role']);
        Session::set('user_email',$user['email']);

        // Store active term in session for display in header
        $activeTerm = DB::queryOne(
            "SELECT t.name, ay.year_name FROM terms t
             JOIN academic_years ay ON t.academic_year_id = ay.id
             WHERE t.is_active = 1 LIMIT 1"
        );
        if ($activeTerm) {
            Session::set('active_term', $activeTerm['year_name'] . ' · ' . $activeTerm['name']);
        }
    }

    private function updateLastLogin(int $userId): void {
        DB::execute("UPDATE users SET last_login_at = NOW() WHERE id = ?", [$userId]);
    }

    private function isLockedOut(string $identifier): bool {
        $cutoff   = date('Y-m-d H:i:s', strtotime('-' . LOGIN_LOCKOUT_MINS . ' minutes'));
        $attempts = DB::queryOne(
            "SELECT COUNT(*) as cnt FROM login_attempts
             WHERE identifier = ? AND attempted_at > ?",
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

    private function redirect(string $path): never {
        $base = defined('APP_BASE') ? APP_BASE : '';
        header("Location: {$base}{$path}");
        exit;
    }
}
