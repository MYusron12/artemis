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
        $accessToken = $request->header('Authorization');

        if (!$timestamp || !$clientKey || !$signature || !$accessToken) {
            Response::error('Invalid Mandatory Field', 400, '502');
        }

        if ($clientKey !== Env::get('SNAP_CLIENT_ID')) {
            Response::error('Unauthorized', 401, '401');
        }

        // Ambil token dari header Authorization
        $token = str_replace('Bearer ', '', $accessToken);

        // Verifikasi symmetric signature
        $method   = $_SERVER['REQUEST_METHOD'];
        $endpoint = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $body     = json_encode($request->body()) ?: '{}';

        $expected = Signature::generateSymmetric(
            $method,
            $endpoint,
            $token,
            $body,
            $timestamp,
            Env::get('SNAP_CLIENT_SECRET')
        );

        if ($signature !== $expected) {
            Response::error('Unauthorized. Invalid Signature', 401, '401');
        }

        $next();
    }
}