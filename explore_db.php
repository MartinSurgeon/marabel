<?php
require_once 'private/src/Helpers/DB.php';
$res = DB::query("SHOW TABLES");
foreach($res as $r) {
    echo array_values($r)[0] . "\n";
}
