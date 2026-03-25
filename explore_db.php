<?php
require 'private/src/Helpers/DB.php';
try {
    $res = DB::query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role");
    foreach ($res as $row) {
        echo "{$row['role']}: {$row['cnt']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
