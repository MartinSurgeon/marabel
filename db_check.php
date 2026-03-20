<?php
// Temporary debug helper (CLI) to inspect stored password hash for the seeded admin user.
require __DIR__ . '/private/config/database.php';

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    DB_HOST,
    DB_PORT,
    DB_NAME,
    DB_CHARSET
);

$pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$stmt = $pdo->prepare(
    'SELECT id, full_name, email, role, is_active, password_hash
     FROM users
     WHERE email = ?
     LIMIT 1'
);
$stmt->execute(['admin@uaddara.edu.gh']);

$row = $stmt->fetch(PDO::FETCH_ASSOC);
var_export($row);

echo PHP_EOL . 'password_verify(password123)=' .
    (is_array($row) && isset($row['password_hash'])
        ? (password_verify('password123', $row['password_hash']) ? 'true' : 'false')
        : 'n/a');

