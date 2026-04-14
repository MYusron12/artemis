<?php

namespace App\Middlewares;

use Artemis\Middleware;
use Artemis\Request;
use Artemis\Response;
use Artemis\RateLimiter;
use Artemis\Env;

class RateLimitMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): void
    {
        $ip         = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $maxRequest = (int) Env::get('RATE_LIMIT_MAX', 60);
        $window     = (int) Env::get('RATE_LIMIT_WINDOW', 60);

        if (!RateLimiter::check($ip, $maxRequest, $window)) {
            $remaining = RateLimiter::remaining($ip, $maxRequest, $window);

            header('X-RateLimit-Limit: ' . $maxRequest);
            header('X-RateLimit-Remaining: ' . $remaining);
            header('X-RateLimit-Window: ' . $window);

            Response::error('Too Many Requests', 429, '429');
        }

        header('X-RateLimit-Limit: ' . $maxRequest);
        header('X-RateLimit-Remaining: ' . RateLimiter::remaining($ip, $maxRequest, $window));

        $next();
    }
}