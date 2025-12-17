<?php
/**
 * Database Configuration
 *
 * This class provides a singleton PDO instance for database connections.
 * It loads database credentials from environment variables using Dotenv.
 * The connection is configured for MySQL with proper error handling and security settings.
 *
 * Environment Variables Required:
 * - DB_HOST: Database server hostname (default: 127.0.0.1)
 * - DB_NAME: Database name
 * - DB_USER: Database username
 * - DB_PASS: Database password
 * - DB_CHARSET: Character set (default: utf8mb4)
 */

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
     * Uses singleton pattern to ensure only one connection per request.
     *
     * @return PDO The database connection instance
     * @throws PDOException If connection fails
     */
    public static function getInstance(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::bootstrapEnv();

        // Load database configuration from environment variables with defaults
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $db = $_ENV['DB_NAME'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        // Build DSN for MySQL connection
        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Return associative arrays
            PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
        ];

        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode());
        }

        return self::$instance;
    }

    /**
     * Loads environment variables from .env file if not already loaded.
     * Checks for APP_ENV to avoid double-loading.
     */
    private static function bootstrapEnv(): void
    {
        if (isset($_ENV['APP_ENV'])) {
            return; // Already loaded
        }

        // Find project root (two levels up from config directory)
        $root = dirname(__DIR__, 2);

        // Load Composer autoloader if available
        if (file_exists($root . '/vendor/autoload.php')) {
            require_once $root . '/vendor/autoload.php';
        }

        // Load environment variables using Dotenv if class exists
        if (class_exists(Dotenv::class)) {
            $dotenv = Dotenv::createImmutable($root);
            $dotenv->safeLoad();
        }
    }
}
}




