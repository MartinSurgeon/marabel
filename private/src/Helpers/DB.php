<?php
/**
 * PDO Singleton Helper
 * Uaddara Basic School — SBA Management System
 */

require_once __DIR__ . '/../../config/database.php';

class DB {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
        return self::$instance;
    }

    /**
     * Run a prepared SELECT query and return all rows.
     */
    public static function query(string $sql, array $params = []): array {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Run a prepared SELECT query and return the first row.
     */
    public static function queryOne(string $sql, array $params = []): ?array {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Run a prepared SELECT query and return a single value from the first column.
     */
    public static function queryValue(string $sql, array $params = []): mixed {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Run INSERT / UPDATE / DELETE and return affected rows count.
     */
    public static function execute(string $sql, array $params = []): int {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Run INSERT and return last inserted ID.
     */
    public static function insert(string $sql, array $params = []): int {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) self::getInstance()->lastInsertId();
    }

    /**
     * Begin a transaction.
     */
    public static function beginTransaction(): void {
        self::getInstance()->beginTransaction();
    }

    public static function commit(): void {
        self::getInstance()->commit();
    }

    public static function rollback(): void {
        self::getInstance()->rollBack();
    }
}
