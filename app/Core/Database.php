<?php

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    private function __construct() {}
    private function __clone() {}

    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = self::$config;
            if (empty($config)) {
                $dbConfigFile = dirname(__DIR__, 2) . '/config/database.php';
                if (file_exists($dbConfigFile)) {
                    $allConfig = require $dbConfigFile;
                    $config = $allConfig['connections'][$allConfig['default']] ?? [];
                }
            }

            $driver   = $config['driver'] ?? 'mysql';
            $host     = $config['host'] ?? '127.0.0.1';
            $port     = $config['port'] ?? '3306';
            $database = $config['database'] ?? 'socialnet';
            $username = $config['username'] ?? 'root';
            $password = $config['password'] ?? '';
            $charset  = $config['charset'] ?? 'utf8mb4';
            $options  = $config['options'] ?? [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $dsn = "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";

            try {
                self::$instance = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                // Log and throw clean message
                error_log("Database Connection Error: " . $e->getMessage());
                throw new RuntimeException("Database connection failed. Please check MySQL server and credentials.");
            }
        }

        return self::$instance;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }

    public static function insert(string $sql, array $params = []): string|false
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $pdo->lastInsertId();
    }

    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool
    {
        return self::getConnection()->rollBack();
    }
}
