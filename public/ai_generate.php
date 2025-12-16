<?php

declare(strict_types=1);

use App\Includes;
use App\Services\OpenAIClient;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$title = trim($input['title'] ?? '');
$csrf = $input['csrf_token'] ?? '';

if (!Includes\verify_csrf($csrf)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

if ($title === '') {
    echo json_encode(['success' => false, 'error' => 'Title is required']);
    exit;
}

try {
    $client = new OpenAIClient();
    $content = $client->generateContent($title);
    echo json_encode(['success' => true, 'content' => $content]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;




