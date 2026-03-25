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
define('SMS_PORTAL_URL',         'https://school.portal'); // Replace with live URL

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
    1 => ['label' => 'HIGHLY PROFICIENT',      'abbr' => 'HP', 'min' => 80,  'max' => 100,   'range' => '80% +'],
    2 => ['label' => 'PROFICIENT',             'abbr' => 'P',  'min' => 68,  'max' => 79.99, 'range' => '68-79%'],
    3 => ['label' => 'APPROACHING PROFICIENCY','abbr' => 'AP', 'min' => 54,  'max' => 67.99, 'range' => '54-67%'],
    4 => ['label' => 'DEVELOPING',             'abbr' => 'D',  'min' => 40,  'max' => 53.99, 'range' => '40-53%'],
    5 => ['label' => 'EMERGING',               'abbr' => 'E',  'min' => 0,   'max' => 39.99, 'range' => '39% AND BELOW'],
]);

// WAEC / BECE Grading Scale (Standard 1-9)
define('WAEC_SCALE', [
    1 => ['label' => 'EXCELLENT',      'abbr' => '1', 'min' => 80,  'max' => 100,   'range' => '80-100'],
    2 => ['label' => 'VERY GOOD',      'abbr' => '2', 'min' => 70,  'max' => 79.99, 'range' => '70-79'],
    3 => ['label' => 'GOOD',           'abbr' => '3', 'min' => 60,  'max' => 69.99, 'range' => '60-69'],
    4 => ['label' => 'HIGH CREDIT',    'abbr' => '4', 'min' => 55,  'max' => 59.99, 'range' => '55-59'],
    5 => ['label' => 'CREDIT',         'abbr' => '5', 'min' => 50,  'max' => 54.99, 'range' => '50-54'],
    6 => ['label' => 'LOW CREDIT',     'abbr' => '6', 'min' => 45,  'max' => 49.99, 'range' => '45-49'],
    7 => ['label' => 'PASS',           'abbr' => '7', 'min' => 40,  'max' => 44.99, 'range' => '40-44'],
    8 => ['label' => 'WEAK PASS',      'abbr' => '8', 'min' => 35,  'max' => 39.99, 'range' => '35-39'],
    9 => ['label' => 'FAIL',           'abbr' => '9', 'min' => 0,   'max' => 34.99, 'range' => '0-34'],
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
