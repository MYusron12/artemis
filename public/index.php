<?php

require __DIR__ . '/../vendor/autoload.php';

use Artemis\Router;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/' || $uri === '') {
    readfile(__DIR__ . '/index.html');
    exit;
}

$router = new Router();

require __DIR__ . '/../routes/api.php';

$router->dispatch();