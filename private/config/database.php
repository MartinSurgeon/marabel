<?php
/**
 * Database Configuration
 * Uaddara Basic School — SBA Management System
 *
 * Defaults below are for local XAMPP. On a server, put that server's
 * credentials in database.local.php (same folder, NOT in git) — it is
 * loaded first, so a `git pull` never overwrites the live login.
 */

if (file_exists(__DIR__ . '/database.local.php')) {
    require __DIR__ . '/database.local.php';
}

defined('DB_HOST')    || define('DB_HOST',    'localhost');
defined('DB_NAME')    || define('DB_NAME',    'uaddara_sba');
defined('DB_USER')    || define('DB_USER',    'root');
defined('DB_PASS')    || define('DB_PASS',    '');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');
defined('DB_PORT')    || define('DB_PORT',    3306);
