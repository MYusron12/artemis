<?php

namespace App\Middlewares;

use Artemis\Middleware;
use Artemis\Request;
use Artemis\Log;

class LogMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = $_SERVER['REQUEST_URI'];
        $ip     = $_SERVER['REMOTE_ADDR'] ?? '-';
        $start  = microtime(true);

        Log::info("START $method $uri from $ip");

        $next();

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::info("END $method $uri — {$duration}ms");
    }
}