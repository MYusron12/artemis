<?php

require __DIR__ . '/../vendor/autoload.php';

use Artemis\Router;
use Artemis\Database;
use Artemis\ErrorHandler;
use Artemis\Env;
use Artemis\Log;

Env::load(__DIR__ . '/../.env');
ErrorHandler::register();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/' || $uri === '') {
    readfile(__DIR__ . '/index.html');
    exit;
}

Log::request();

Database::connect();

$router = new Router();

require __DIR__ . '/../routes/api.php';

$router->dispatch();