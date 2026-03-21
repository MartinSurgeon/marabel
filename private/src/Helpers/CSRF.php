<?php
/**
 * CSRF Token Helper
 * Uaddara Basic School — SBA Management System
 */

class CSRF {

    private static string $key = '_csrf_token';

    public static function generate(): string {
        if (empty($_SESSION[self::$key])) {
            $_SESSION[self::$key] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$key];
    }

    public static function token(): string {
        return self::generate();
    }

    /**
     * Output a hidden input field with the CSRF token.
     */
    public static function field(): string {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(self::token()) . '">';
    }

    /**
     * Validate the submitted CSRF token.
     * Token persists for the session (not single-use) to support pages with multiple forms.
     */
    public static function verify(): bool {
        $submitted = $_POST['_csrf_token'] ?? '';
        $expected  = $_SESSION[self::$key] ?? '';

        if (!$submitted || !$expected || !hash_equals($expected, $submitted)) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                http_response_code(403);
                die('CSRF token mismatch.');
            }
            return false;
        }
        // Token is NOT rotated — kept for the lifetime of the session.
        // This supports pages with multiple forms (login: 3 forms, OTP page: 2 forms).
        return true;
    }
}
