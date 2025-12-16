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
$errors = [];

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function handleImageUpload(array $file): ?string
{
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload error: ' . $file['error']);
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('File too large. Max 2MB.');
    }

    $allowed = ['jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('Invalid file extension.');
    }

    // Validate file type using finfo if available, otherwise use getimagesize
    $isValidImage = false;
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowedMime = ['image/jpeg', 'image/png'];
        $isValidImage = in_array($mime, $allowedMime, true);
    } else {
        // Fallback: use getimagesize to validate it's actually an image
        $imageInfo = @getimagesize($file['tmp_name']);
        $isValidImage = $imageInfo !== false && in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true);
    }

    if (!$isValidImage) {
        throw new RuntimeException('Invalid file type. File must be a valid JPEG or PNG image.');
    }

    $safeName = uniqid('img_', true) . '.' . $ext;
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $destination = $uploadDir . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return $safeName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';

    if (!Includes\verify_csrf($csrf)) {
        $errors[] = 'Invalid CSRF token.';
    }

    if ($title === '' || $content === '') {
        $errors[] = 'Title and content are required.';
    }

    $imageName = null;
    if (!$errors) {
        try {
            $imageName = handleImageUpload($_FILES['image'] ?? []);
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (!$errors && $postModel->create($title, $content, $imageName, (int) $_SESSION['user_id'])) {
        header('Location: /dashboard.php');
        exit;
    } elseif (!$errors) {
        $errors[] = 'Failed to create post.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Post - Mini CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/dashboard.php">Mini CMS</a>
        <div>
            <a href="/dashboard.php" class="btn btn-outline-light btn-sm me-2">Back</a>
            <a href="/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Create Post</h4>
                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-danger"><?= h($error) ?></div>
                    <?php endforeach; ?>
                    <form method="post" enctype="multipart/form-data" id="postForm">
                        <input type="hidden" name="csrf_token" value="<?= Includes\csrf_token(); ?>">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required value="<?= h($_POST['title'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0">Content</label>
                                <button type="button" class="btn btn-sm btn-secondary" id="generateBtn">Generate Content</button>
                            </div>
                            <textarea name="content" class="form-control" rows="8" required><?= h($_POST['content'] ?? '') ?></textarea>
                            <div class="form-text" id="aiStatus"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image (JPG/PNG, &lt;2MB)</label>
                            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png">
                        </div>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const generateBtn = document.getElementById('generateBtn');
const aiStatus = document.getElementById('aiStatus');
generateBtn?.addEventListener('click', async () => {
    const title = document.querySelector('input[name="title"]').value.trim();
    const csrf = document.querySelector('input[name="csrf_token"]').value;
    if (!title) {
        aiStatus.textContent = 'Please enter a title first.';
        aiStatus.className = 'form-text text-danger';
        return;
    }
    aiStatus.textContent = 'Generating...';
    aiStatus.className = 'form-text text-muted';
    try {
        const response = await fetch('/ai_generate.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({title, csrf_token: csrf})
        });
        const data = await response.json();
        if (data.success) {
            document.querySelector('textarea[name="content"]').value = data.content;
            aiStatus.textContent = 'Content generated. Review before saving.';
            aiStatus.className = 'form-text text-success';
        } else {
            aiStatus.textContent = data.error || 'Failed to generate content.';
            aiStatus.className = 'form-text text-danger';
        }
    } catch (e) {
        aiStatus.textContent = 'Error contacting AI service.';
        aiStatus.className = 'form-text text-danger';
    }
});
</script>
</body>
</html>




