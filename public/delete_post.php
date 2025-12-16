<?php

declare(strict_types=1);

use App\Includes;
use App\Models\Post;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

Includes\requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$csrf = $_GET['csrf_token'] ?? '';

if (!Includes\verify_csrf($csrf)) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

$postModel = new Post();
if ($id > 0) {
    $postModel->delete($id);
}

header('Location: /dashboard.php');
exit;




