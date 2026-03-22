<?php
require_once __DIR__ . '/private/src/Helpers/DB.php';
$duplicates = DB::query("SELECT student_id, COUNT(*) as cnt FROM student_parents GROUP BY student_id HAVING cnt > 1");
echo json_encode($duplicates);
