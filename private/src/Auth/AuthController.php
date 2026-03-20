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
            'staff'   => $this->staffLogin(),
            'student' => $this->studentLogin(),
            default   => $this->redirect('/login'),
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
