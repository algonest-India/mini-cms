<?php

declare(strict_types=1);

use App\Models\Post;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

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
    <title>Mini CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">Mini CMS</a>
        <div class="d-flex">
            <a href="/login.php" class="btn btn-outline-light btn-sm me-2">Login</a>
            <a href="/register.php" class="btn btn-primary btn-sm">Register</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <?php foreach ($posts as $post): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($post['image'])): ?>
                        <?php
                        $imagePath = __DIR__ . '/uploads/' . $post['image'];
                        if (file_exists($imagePath)):
                        ?>
                            <img src="/uploads/<?= h($post['image']) ?>" class="card-img-top" alt="<?= h($post['title']) ?>" onerror="this.style.display='none';">
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= h($post['title']) ?></h5>
                        <p class="card-text"><?= h(mb_strimwidth($post['content'], 0, 200, '...')) ?></p>
                        <div class="mt-auto">
                            <small class="text-muted d-block mb-2">By <?= h($post['author'] ?? 'Unknown') ?></small>
                            <a href="/view_post.php?id=<?= (int) $post['id'] ?>" class="btn btn-sm btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?>
            <div class="col-12">
                <div class="alert alert-info">No posts yet. Check back soon.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>




