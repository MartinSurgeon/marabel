<?php
/**
 * Session Management Helper
 * Uaddara Basic School — SBA Management System
 */

class Session {

    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
        self::checkTimeout();
    }

    private static function checkTimeout(): void {
        if (isset($_SESSION['last_activity'])) {
            $idle = time() - $_SESSION['last_activity'];
            if ($idle > SESSION_TIMEOUT_MINUTES * 60) {
                self::destroy();
                $base = defined('APP_BASE') ? APP_BASE : '';
                header('Location: ' . $base . '/login?timeout=1');
                exit;
            }
        }
        $_SESSION['last_activity'] = time();
    }

    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function delete(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void {
        session_unset();
        session_destroy();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
    }

    /** Flash message: set once, read once */
    public static function flash(string $key, mixed $value = null): mixed {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    /** Check if currently logged in */
    public static function isLoggedIn(): bool {
        return self::has('user_id') && self::has('user_role');
    }

    /** Get current user's role */
    public static function role(): ?string {
        return self::get('user_role');
    }

    /** Get current user's ID */
    public static function userId(): ?int {
        return self::get('user_id');
    }

    /** Require a specific role or redirect to login */
    public static function requireRole(string ...$roles): void {
        $base = defined('APP_BASE') ? APP_BASE : '';
        if (!self::isLoggedIn() || !in_array(self::role(), $roles, true)) {
            header('Location: ' . $base . '/login');
            exit;
        }
    }

    /** Require any authenticated user */
    public static function requireAuth(): void {
        $base = defined('APP_BASE') ? APP_BASE : '';
        if (!self::isLoggedIn()) {
            header('Location: ' . $base . '/login');
            exit;
        }
    }

    /** Regenerate session ID (call on privilege escalation) */
    public static function regenerate(): void {
        session_regenerate_id(true);
    }
}
