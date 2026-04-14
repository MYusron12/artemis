<?php

namespace App\Middlewares;

use Artemis\Middleware;
use Artemis\Request;

class CorsMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CLIENT-KEY, X-TIMESTAMP, X-SIGNATURE, X-PARTNER-ID, X-EXTERNAL-ID, CHANNEL-ID');

        // Handle preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $next();
    }
}