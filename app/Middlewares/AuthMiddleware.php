<?php

namespace App\Middlewares;

use Artemis\Middleware;
use Artemis\Request;
use Artemis\Response;

class AuthMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): void
    {
        $token = $request->header('Authorization');

        if (!$token || $token !== 'Bearer secret-token') {
            Response::error('Unauthorized', 401, '401');
        }

        $next();
    }
}