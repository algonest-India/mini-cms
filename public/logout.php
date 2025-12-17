<?php

declare(strict_types=1);

use App\Includes;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

Includes\logoutUser();
header('Location: /login.php');
exit;

