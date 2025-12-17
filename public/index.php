<?php
/**
 * Index Page - Main Landing Page of the Mini CMS
 *
 * This file serves as the homepage of the Mini CMS application. It displays a list of all published posts
 * in a card-based layout. The page adapts its navigation and content based on the user's login status:
 * - Logged-in users see a welcome message, dashboard link, logout option, and a button to create new posts.
 * - Non-logged-in users see login and register buttons.
 *
 * Features:
 * - Fetches all posts from the database using the Post model.
 * - Displays post cards with title, content preview, author, and optional image.
 * - Uses Bootstrap for responsive design.
 * - Includes security measures like htmlspecialchars for output escaping.
 */

declare(strict_types=1);

// Import necessary classes and namespaces
use App\Includes; // For authentication functions like isLoggedIn()
use App\Models\Post; // Post model for database operations
use Dotenv\Dotenv; // For loading environment variables

// Include Composer autoloader to load dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file in the project root
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Initialize Post model and fetch all posts from the database
$postModel = new Post();
$posts = $postModel->getAll();

/**
 * Helper function to escape HTML output for security
 *
 * @param string|null $value The string to escape
 * @return string The escaped string
 */
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
<!-- Navigation Bar - Adapts based on user login status -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">Mini CMS</a>
        <div class="d-flex">
            <?php if (Includes\isLoggedIn()): ?>
                <!-- Display for logged-in users -->
                <span class="navbar-text me-3">Welcome, <?= h(Includes\currentUserName()) ?>!</span>
                <a href="/dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                <a href="/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            <?php else: ?>
                <!-- Display for non-logged-in users -->
                <a href="/login.php" class="btn btn-outline-light btn-sm me-2">Login</a>
                <a href="/register.php" class="btn btn-primary btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
    <!-- Show create post button only for logged-in users -->
    <?php if (Includes\isLoggedIn()): ?>
        <div class="mb-4">
            <a href="/create_post.php" class="btn btn-success">Create New Post</a>
        </div>
    <?php endif; ?>
    <!-- Posts Grid - Displays all published posts -->
    <div class="row">
        <?php foreach ($posts as $post): ?>
            <!-- Individual Post Card -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <!-- Display post image if available -->
                    <?php if (!empty($post['image'])): ?>
                        <?php
                        // Check if image file exists on server
                        $imagePath = __DIR__ . '/uploads/' . $post['image'];
                        if (file_exists($imagePath)):
                        ?>
                            <img src="/uploads/<?= h($post['image']) ?>" class="card-img-top" alt="<?= h($post['title']) ?>" onerror="this.style.display='none';">
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <!-- Post title -->
                        <h5 class="card-title"><?= h($post['title']) ?></h5>
                        <!-- Post content preview (truncated to 200 characters) -->
                        <p class="card-text"><?= h(mb_strimwidth($post['content'], 0, 200, '...')) ?></p>
                        <div class="mt-auto">
                            <!-- Author name -->
                            <small class="text-muted d-block mb-2">By <?= h($post['author'] ?? 'Unknown') ?></small>
                            <!-- Link to full post view -->
                            <a href="/view_post.php?id=<?= (int) $post['id'] ?>" class="btn btn-sm btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <!-- Message when no posts exist -->
        <?php if (empty($posts)): ?>
            <div class="col-12">
                <div class="alert alert-info">No posts yet. Check back soon.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>




