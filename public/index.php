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

$allowedOrigins = explode(',', Env::get('CORS_ORIGIN', '*'));
$origin         = $_SERVER['HTTP_ORIGIN'] ?? '';

if (empty($origin)) {
    header('Access-Control-Allow-Origin: *');
} elseif (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode([
        'responseCode'    => '403M403',
        'responseMessage' => 'Forbidden. Origin not allowed',
    ]);
    exit;
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CLIENT-KEY, X-TIMESTAMP, X-SIGNATURE, X-PARTNER-ID, X-EXTERNAL-ID, CHANNEL-ID');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

Log::request();

Database::connect();

$router = new Router();

require __DIR__ . '/../routes/api.php';

$router->dispatch();