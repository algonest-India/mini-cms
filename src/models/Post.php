<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class Post
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT p.*, u.name AS author FROM posts p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, u.name AS author FROM posts p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        return $post ?: null;
    }

    public function create(string $title, string $content, ?string $image, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO posts (title, content, image, user_id, created_at, updated_at) 
             VALUES (:title, :content, :image, :user_id, NOW(), NOW())'
        );

        return $stmt->execute([
            'title' => $title,
            'content' => $content,
            'image' => $image,
            'user_id' => $userId,
        ]);
    }

    public function update(int $id, string $title, string $content, ?string $image = null): bool
    {
        $sql = 'UPDATE posts SET title = :title, content = :content, updated_at = NOW()';
        $params = [
            'title' => $title,
            'content' => $content,
            'id' => $id,
        ];

        if ($image !== null) {
            $sql .= ', image = :image';
            $params['image'] = $image;
        }

        $sql .= ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}




