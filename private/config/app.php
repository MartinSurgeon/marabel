<?php
/**
 * Application Configuration
 * Uaddara Basic School — SBA Management System
 */

// ── Environment ────────────────────────────────────────────────────
define('APP_ENV',        'development'); // 'production' on live server
define('APP_DEBUG',      APP_ENV === 'development');
define('APP_KEY',        'change-this-to-a-random-64-char-string');

// ── School Identity ─────────────────────────────────────────────────
define('SCHOOL_NAME',        'Uaddara Basic School');
define('SCHOOL_BODY',        'Armed Forces Education Unit');
define('SCHOOL_LOCATION',    'Kumasi, Ghana');
define('SCHOOL_SMS_SENDER',  'Fabric Flow'); // max 11 chars
define('SCHOOL_LOGO',        '/assets/img/school-logo.png');

// ── Paths (defined in index.php before this file is loaded) ──────────
// ROOT_PATH, PRIVATE_PATH, ASSETS_URL are set by the front controller.
// Only add derived paths that aren't in index.php:
if (!defined('PUBLIC_PATH'))  define('PUBLIC_PATH',  ROOT_PATH);
if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH', ROOT_PATH . '/uploads');

// ── Session ─────────────────────────────────────────────────────────
define('SESSION_TIMEOUT_MINUTES', 30);
define('SESSION_NAME', 'UADDARA_SBA_SESS');

// ── Roles ───────────────────────────────────────────────────────────
define('ROLE_ADMIN',   'admin');
define('ROLE_TEACHER', 'teacher');
define('ROLE_PARENT',  'parent');
define('ROLE_STUDENT', 'student');

// ── SMS (Zenoph) ─────────────────────────────────────────────────────
define('SMS_API_KEY',  '9bb2205bfa7ab3fa695254e68bf44fbcb0ecdaed0757612f9474d167be9a5ccd'); // Set your Zenoph API key here
define('SMS_HOST',     'api.smsonlinegh.com');
define('SMS_OTP_EXPIRY_MINUTES', 10);
define('SMS_OTP_RATE_LIMIT',     3);   // max OTPs per phone per hour

// ── Scoring ─────────────────────────────────────────────────────────
define('SBA_COMPONENT_MAX',    15);   // each component max
define('SBA_COMPONENTS_TOTAL', 60);   // sum max (4 × 15)
define('SBA_CLASS_SCORE_MAX',  50);   // scaled class score
define('SBA_EXAM_RAW_MAX',     100);  // exam entered out of
define('SBA_EXAM_SCORE_MAX',   50);   // scaled exam score
define('SBA_OVERALL_MAX',      100);  // class score + exam score

// ── Grading ─────────────────────────────────────────────────────────
// Level of Proficiency — fixed, not configurable
define('PROFICIENCY_SCALE', [
    1 => ['label' => 'HIGHLY PROFICIENT',      'abbr' => 'HP', 'min' => 80,  'max' => 100],
    2 => ['label' => 'PROFICIENT',             'abbr' => 'P',  'min' => 68,  'max' => 79.99],
    3 => ['label' => 'APPROACHING PROFICIENCY','abbr' => 'AP', 'min' => 54,  'max' => 67.99],
    4 => ['label' => 'DEVELOPING',             'abbr' => 'D',  'min' => 40,  'max' => 53.99],
    5 => ['label' => 'EMERGING',               'abbr' => 'E',  'min' => 0,   'max' => 39.99],
]);

// ── Upload Limits ───────────────────────────────────────────────────
define('MAX_PHOTO_SIZE_MB', 2);
define('MAX_EXCEL_SIZE_MB', 10);
define('ALLOWED_PHOTO_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_EXCEL_TYPES', [
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
]);

// ── Security ────────────────────────────────────────────────────────
define('BCRYPT_COST',        12);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINS', 15);
