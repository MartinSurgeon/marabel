<?php
/**
 * Test-only router for `php -S`. Points the app at the throwaway test database
 * and emulates .htaccess. Never deploy behind a real web server.
 */
define('DB_NAME', 'uaddara_sba_test');   // database.php's later define() is ignored
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/output/php_errors.log');
error_reporting(E_ALL);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/__whichdb') {
    require dirname(__DIR__) . '/config/database.php';
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    exit($pdo->query('SELECT DATABASE()')->fetchColumn());
}
if (preg_match('#^/(private|\.git)(/|$)#', $path)) { http_response_code(403); exit('Forbidden'); }

$root = dirname(__DIR__, 2);
if ($path !== '/' && is_file($root . $path)) return false;
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $root . '/index.php';
