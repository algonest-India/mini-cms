<?php

declare(strict_types=1);

namespace App\Config;

use Dotenv\Dotenv;
use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;

    /**
     * Returns a shared PDO instance using environment variables.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::bootstrapEnv();

        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $db = $_ENV['DB_NAME'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode());
        }

        return self::$instance;
    }

    private static function bootstrapEnv(): void
    {
        if (isset($_ENV['APP_ENV'])) {
            return;
        }

        $root = dirname(__DIR__, 2);
        if (file_exists($root . '/vendor/autoload.php')) {
            require_once $root . '/vendor/autoload.php';
        }

        if (class_exists(Dotenv::class)) {
            $dotenv = Dotenv::createImmutable($root);
            $dotenv->safeLoad();
        }
    }
}




