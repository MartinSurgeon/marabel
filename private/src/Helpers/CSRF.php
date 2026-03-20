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
     * Throws on failure in debug mode; returns false in production.
     */
    public static function verify(): bool {
        $submitted = $_POST['_csrf_token'] ?? '';
        $expected  = $_SESSION[self::$key] ?? '';

        if (!$submitted || !$expected || !hash_equals($expected, $submitted)) {
            // Guard against missing configuration constants during early bootstrap.
            if (defined('APP_DEBUG') && APP_DEBUG) {
                http_response_code(403);
                die('CSRF token mismatch.');
            }
            return false;
        }
        // Rotate the token after successful verification
        unset($_SESSION[self::$key]);
        return true;
    }
}
