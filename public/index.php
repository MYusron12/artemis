<?php

require __DIR__ . '/../vendor/autoload.php';

use Artemis\Router;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Kalau akses root, tampilkan landing page
if ($uri === '/' || $uri === '') {
    readfile(__DIR__ . '/index.html');
    exit;
}

// Selain itu, jalankan framework
$router = new Router();

require __DIR__ . '/../routes/api.php';

$router->dispatch();