<?php

declare(strict_types=1);

use App\Models\Post;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$postModel = new Post();
$id = (int) ($_GET['id'] ?? 0);
$post = $postModel->find($id);

if (!$post) {
    http_response_code(404);
    echo 'Post not found';
    exit;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatDate(?string $date): string
{
    if (!$date) {
        return 'Unknown';
    }
    $timestamp = strtotime($date);
    return date('F j, Y', $timestamp);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($post['title']) ?> - Mini CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">Mini CMS</a>
        <div class="d-flex">
            <a href="/" class="btn btn-outline-light btn-sm me-2">Back to Posts</a>
            <a href="/login.php" class="btn btn-outline-light btn-sm me-2">Login</a>
            <a href="/register.php" class="btn btn-primary btn-sm">Register</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <article class="card shadow-sm">
                <?php if (!empty($post['image'])): ?>
                    <?php
                    $imagePath = __DIR__ . '/uploads/' . $post['image'];
                    if (file_exists($imagePath)):
                    ?>
                        <img src="/uploads/<?= h($post['image']) ?>" class="card-img-top post-image" alt="<?= h($post['title']) ?>" onerror="this.style.display='none';">
                    <?php endif; ?>
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title mb-3"><?= h($post['title']) ?></h1>
                    <div class="text-muted mb-4">
                        <small>
                            By <strong><?= h($post['author'] ?? 'Unknown') ?></strong> | 
                            Published on <?= formatDate($post['created_at']) ?>
                            <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                | Updated on <?= formatDate($post['updated_at']) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div class="post-content">
                        <?= nl2br(h($post['content'])) ?>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <a href="/" class="btn btn-secondary">← Back to All Posts</a>
                    </div>
                </div>
            </article>
        </div>
    </div>
</div>
</body>
</html>

