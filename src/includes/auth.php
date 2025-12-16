<?php

declare(strict_types=1);

namespace App\Includes;

use App\Models\User;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Regenerates session ID to mitigate fixation.
 */
function secure_session_regenerate(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function currentUserName(): ?string
{
    return $_SESSION['user_name'] ?? null;
}

function loginUser(string $email, string $password): bool
{
    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    secure_session_regenerate();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];

    return true;
}

function registerUser(string $name, string $email, string $password): bool
{
    $userModel = new User();
    if ($userModel->findByEmail($email)) {
        return false; // existing user
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    return $userModel->create($name, $email, $hashed);
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}




