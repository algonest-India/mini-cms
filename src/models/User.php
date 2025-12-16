<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(string $name, string $email, string $password): bool
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, NOW())');
        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }
}




