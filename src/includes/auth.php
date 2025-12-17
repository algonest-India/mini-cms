<?php
/**
 * Authentication Functions
 *
 * This file contains all authentication-related functions for the Mini CMS.
 * It handles user login, logout, registration, session management, and CSRF protection.
 *
 * Key Features:
 * - Session-based authentication with secure session regeneration
 * - CSRF token generation and verification for form security
 * - Password hashing using bcrypt
 * - User login status checking
 * - Automatic redirection for protected pages
 */

declare(strict_types=1);

namespace App\Includes;

use App\Models\User;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Regenerates session ID to mitigate session fixation attacks.
 */
function secure_session_regenerate(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Generates or retrieves a CSRF token for form protection.
 *
 * @return string The CSRF token
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Verifies a CSRF token against the stored one.
 *
 * @param string $token The token to verify
 * @return bool True if valid, false otherwise
 */
function verify_csrf(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Requires the user to be logged in, redirects to login page if not.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Checks if a user is currently logged in.
 *
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Gets the current logged-in user's name.
 *
 * @return string|null The user's name or null if not logged in
 */
function currentUserName(): ?string
{
    return $_SESSION['user_name'] ?? null;
}

/**
 * Attempts to log in a user with email and password.
 *
 * @param string $email User's email
 * @param string $password User's password
 * @return bool True on successful login, false otherwise
 */
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

/**
 * Registers a new user with name, email, and password.
 *
 * @param string $name User's full name
 * @param string $email User's email
 * @param string $password User's password
 * @return bool True on successful registration, false if email already exists
 */
function registerUser(string $name, string $email, string $password): bool
{
    $userModel = new User();
    if ($userModel->findByEmail($email)) {
        return false; // existing user
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    return $userModel->create($name, $email, $hashed);
}

/**
 * Logs out the current user by destroying the session.
 */
function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}




