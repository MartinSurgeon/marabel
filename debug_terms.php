<?php
require 'private/src/Helpers/DB.php';
require 'private/config/config.php';
$res = DB::query("SELECT id, term_name, is_active FROM terms");
header('Content-Type: application/json');
echo json_encode($res);
