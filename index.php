<?php
/**
 * Front Controller / Router
 * Uaddara Basic School — SBA Management System
 */

// ── Bootstrap ────────────────────────────────────────────────────────
define('ROOT_PATH',    __DIR__);
define('PRIVATE_PATH', __DIR__ . '/private');
define('ASSETS_URL',   '/assets');

// Base URL path for subdirectory installs (e.g. '/marabel' or '' for root).
// Using SCRIPT_NAME alone can be inconsistent with rewrite rules, so prefer deriving
// it from the request URL and this folder name.
$appDir   = basename(__DIR__); // expected: 'marabel'
$reqPath  = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$needle   = '/' . $appDir;
if (is_string($reqPath) && $reqPath !== '' && ($reqPath === $needle || strncmp($reqPath, $needle . '/', strlen($needle) + 1) === 0)) {
    define('APP_BASE', $needle);
} else {
    define('APP_BASE', rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\'));
}

require_once PRIVATE_PATH . '/config/database.php';
require_once PRIVATE_PATH . '/config/app.php';

// Autoload helpers
foreach (['DB', 'Session', 'CSRF', 'Validator', 'SMS'] as $helper) {
    require_once PRIVATE_PATH . "/src/Helpers/{$helper}.php";
}

// Autoload engine
require_once PRIVATE_PATH . '/src/Engine/GradingEngine.php';

// Start session
Session::start();

// ── Routing ───────────────────────────────────────────────────────────
$rawUri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip the subdirectory base so routes work at /marabel/ or /
// Detect base path from SCRIPT_NAME (e.g. /marabel/index.php  → /marabel)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$uri      = '/' . ltrim(substr($rawUri, strlen($basePath)), '/');
$uri      = rtrim($uri, '/') ?: '/';
$method   = $_SERVER['REQUEST_METHOD'];

// Route definitions: [pattern, template, roles_allowed, controller]
$routes = [
    // ── Public ──────────────────────────────────────────────
    '/'                         => ['auth/login',          [], 'Auth\\AuthController'],
    '/login'                    => ['auth/login',          [], 'Auth\\AuthController'],
    '/logout'                   => ['auth/logout',         [], 'Auth\\AuthController'],
    '/otp'                      => ['auth/otp',            [], 'Auth\\AuthController'],
    '/debug'                    => ['debug',               [], null],

    // ── Admin ────────────────────────────────────────────────
    '/admin'                    => ['admin/dashboard',     ['admin'], 'Admin\\DashboardController'],
    '/admin/years'              => ['admin/academic_years',['admin'], 'Admin\\AcademicController'],
    '/admin/terms'              => ['admin/terms',         ['admin'], 'Admin\\AcademicController'],
    '/admin/classes'            => ['admin/classes',       ['admin'], 'Admin\\ClassController'],
    '/admin/subjects'           => ['admin/subjects',      ['admin'], 'Admin\\SubjectController'],
    '/admin/teachers'           => ['admin/teachers',      ['admin'], 'Admin\\TeacherController'],
    '/admin/students'           => ['admin/students',      ['admin'], 'Admin\\StudentController'],
    '/admin/import'             => ['admin/import_students',['admin'], 'Admin\\StudentImportController'],
    '/admin/publish'            => ['admin/publish',       ['admin'], 'Admin\\PublishController'],
    '/admin/sms'                => ['admin/sms',           ['admin'], 'Admin\\SMSController'],
    '/admin/promotions'         => ['admin/promotions',    ['admin','teacher'], 'Admin\\PromotionController'],

    // ── Teacher ──────────────────────────────────────────────
    '/teacher'                  => ['teacher/dashboard',   ['admin','teacher'], 'Teacher\\DashboardController'],
    '/teacher/scores'           => ['teacher/score_entry', ['admin','teacher'], 'Teacher\\ScoreController'],
    '/teacher/import'           => ['teacher/import',      ['admin','teacher'], 'Teacher\\ImportController'],
    '/teacher/class'            => ['teacher/manage_class', ['admin','teacher'], 'Teacher\\ClassManagementController'],


    // ── Parent ───────────────────────────────────────────────
    '/parent'                   => ['parent/portal',       ['parent'], 'Parent\\PortalController'],

    // ── Student ──────────────────────────────────────────────
    '/student'                  => ['student/portal',      ['student'], 'Student\\PortalController'],

    // ── Report Card (print view — accessible if published) ──
    '/report'                   => ['report_card/print',   ['admin','teacher','parent','student'], 'Reports\\ReportCardController'],
];

// ── Match route ────────────────────────────────────────────────────
$matched = false;
foreach ($routes as $path => $config) {
    if ($uri === $path) {
        [$template, $roles, $controllerClass] = $config;
        $matched = true;

        // Auth check
        if (!empty($roles)) {
            Session::requireRole(...$roles);
        }

        // Load controller if specified
        if ($controllerClass) {
            $parts      = explode('\\', $controllerClass);
            $dir        = $parts[0];
            $className  = $parts[1] ?? $parts[0];
            $file       = PRIVATE_PATH . "/src/{$dir}/{$className}.php";
            if (file_exists($file)) {
                require_once $file;
                $ctrl = new $className();
                if (method_exists($ctrl, 'handle')) {
                    $ctrl->handle();
                }
            }
        }

        // Render template
        $templateFile = __DIR__ . "/templates/{$template}.php";
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            include __DIR__ . '/templates/errors/404.php';
        }
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    include __DIR__ . '/templates/errors/404.php';
}
