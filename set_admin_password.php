<?php
// Temporary CLI helper to reset the seeded admin password hash.
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

$newHash = password_hash('password123', PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ? LIMIT 1');
$stmt->execute([$newHash, 'admin@uaddara.edu.gh']);

echo "Updated: " . $stmt->rowCount() . " row(s)" . PHP_EOL;
echo "New hash: " . $newHash . PHP_EOL;

