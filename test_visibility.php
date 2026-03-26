<?php
require 'private/config/database.php';
define('ROOT_PATH', __DIR__);
define('PRIVATE_PATH', ROOT_PATH . '/private');
require 'private/config/app.php';
require 'private/src/Helpers/DB.php';
require 'private/src/Admin/TeacherController.php';

echo "Verifying Teacher Visibility Fix...\n";

$_SERVER['REQUEST_METHOD'] = 'GET';
$ctrl = new TeacherController();
$ctrl->handle();

global $teachersList, $activeYearName;

echo "Active Session Name: $activeYearName\n";

$found = false;
foreach ($teachersList as $t) {
    if ($t['subject_count'] > 0) {
        echo "Teacher: {$t['full_name']} | Current Session: {$t['current_year_subjects']} | Total: {$t['subject_count']}\n";
        $found = true;
    }
}

if ($found && $activeYearName !== 'None') {
    echo "SUCCESS: Assignments tracked and Active Session context found.\n";
} else {
    echo "FAIL: Expected assignments or active session not found.\n";
}
