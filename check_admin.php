<?php
require 'private/src/Helpers/DB.php';
require 'private/config/config.php';
$res = DB::query("SELECT email, role FROM users WHERE role = 'admin'");
header('Content-Type: application/json');
echo json_encode($res);
