<?php
/**
 * User Login Page
 *
 * This page handles user authentication for the Mini CMS.
 * It processes login form submissions, validates credentials,
 * and redirects authenticated users to the dashboard.
 *
 * Security Features:
 * - CSRF token validation
 * - Password verification using bcrypt
 * - Session regeneration on successful login
 * - Input sanitization and validation
 */

declare(strict_types=1);

use App\Includes;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Initialize error array for form validation messages
$errors = [];

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    // Verify CSRF token to prevent cross-site request forgery
    if (!Includes\verify_csrf($csrf)) {
        $errors[] = 'Invalid CSRF token.';
    }

    // Attempt login if no errors so far
    if (!$errors && !Includes\loginUser($email, $password)) {
        $errors[] = 'Invalid email or password.';
    }

    // Redirect to dashboard on successful login
    if (!$errors) {
        header('Location: /dashboard.php');
        exit;
    }
}

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
    <title>Login - Mini CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">Mini CMS</a>
        <div>
            <a href="/register.php" class="btn btn-outline-light btn-sm">Register</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Login</h4>
                    <?php foreach ($errors as $error): ?>
                        <div class="alert alert-danger"><?= h($error) ?></div>
                    <?php endforeach; ?>
                    <form method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= Includes\csrf_token(); ?>">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?= h($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>




