<?php

declare(strict_types=1);

use App\Includes;
use App\Models\Post;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

Includes\requireLogin();

$postModel = new Post();
$posts = $postModel->getAll();

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Mini CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">Mini CMS</a>
        <div class="d-flex">
            <span class="navbar-text me-3 text-light">Hello, <?= h(Includes\currentUserName()); ?></span>
            <a href="/create_post.php" class="btn btn-success btn-sm me-2">New Post</a>
            <a href="/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
    </nav>

<div class="container">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="card-title mb-3">Posts</h4>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Excerpt</th>
                            <th>Image</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= h($post['title']); ?></td>
                            <td><?= h(mb_strimwidth($post['content'], 0, 80, '...')); ?></td>
                            <td>
                                <?php if (!empty($post['image'])): ?>
                                    <img src="/uploads/<?= h($post['image']); ?>" alt="<?= h($post['title']); ?>" class="img-thumbnail" style="max-width: 80px;">
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td><?= h($post['updated_at']); ?></td>
                            <td>
                                <a href="/view_post.php?id=<?= (int) $post['id']; ?>" class="btn btn-sm btn-info" target="_blank">View</a>
                                <a href="/edit_post.php?id=<?= (int) $post['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="/delete_post.php?id=<?= (int) $post['id']; ?>&csrf_token=<?= Includes\csrf_token(); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this post?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($posts)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No posts yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>

