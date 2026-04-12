<?php

namespace Artemis\Snap;

use Artemis\Middleware;
use Artemis\Request;
use Artemis\Response;
use Artemis\Env;

class SnapMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): void
    {
        $timestamp   = $request->header('X-TIMESTAMP');
        $clientKey   = $request->header('X-CLIENT-KEY');
        $signature   = $request->header('X-SIGNATURE');

        if (!$timestamp || !$clientKey || !$signature) {
            Response::error('Invalid Mandatory Field', 400, '502');
        }

        if ($clientKey !== Env::get('SNAP_CLIENT_ID')) {
            Response::error('Unauthorized', 401, '401');
        }

        $next();
    }
}