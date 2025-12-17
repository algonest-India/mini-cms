<?php
/**
 * Post Model
 *
 * This class handles all database operations related to blog posts in the Mini CMS.
 * It provides methods for creating, reading, updating, and deleting posts.
 * Posts are associated with users and can include images.
 */

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class Post
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
     * Retrieves all posts with author information, ordered by creation date (newest first).
     *
     * @return array Array of post data including author names
     */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT p.*, u.name AS author FROM posts p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC');
        return $stmt->fetchAll();
    }

    /**
     * Finds a specific post by ID with author information.
     *
     * @param int $id The post ID
     * @return array|null Post data or null if not found
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, u.name AS author FROM posts p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        return $post ?: null;
    }

    /**
     * Creates a new post in the database.
     *
     * @param string $title Post title
     * @param string $content Post content
     * @param string|null $image Optional image filename
     * @param int $userId ID of the user creating the post
     * @return bool True on success, false on failure
     */
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

    /**
     * Updates an existing post.
     *
     * @param int $id Post ID to update
     * @param string $title New title
     * @param string $content New content
     * @param string|null $image Optional new image filename (null to keep existing)
     * @return bool True on success, false on failure
     */
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

    /**
     * Deletes a post from the database.
     *
     * @param int $id Post ID to delete
     * @return bool True on success, false on failure
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM posts WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}




