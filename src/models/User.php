<?php
/**
 * User Model
 *
 * This class handles all database operations related to user accounts in the Mini CMS.
 * It provides methods for user registration and lookup by email.
 * Passwords are stored hashed using bcrypt for security.
 */

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class User
{
    private PDO $db;

    /**
     * Constructor - Initializes database connection.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Creates a new user account.
     *
     * @param string $name User's full name
     * @param string $email User's email address (must be unique)
     * @param string $password Hashed password
     * @return bool True on success, false on failure
     */
    public function create(string $name, string $email, string $password): bool
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, NOW())');
        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * Finds a user by their email address.
     *
     * @param string $email The email to search for
     * @return array|null User data or null if not found
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }
}




