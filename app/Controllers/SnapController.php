<?php

namespace App\Controllers;

use Artemis\Request;
use Artemis\Response;
use Artemis\Env;
use Artemis\Snap\Signature;
use Artemis\Snap\SnapHelper;

class SnapController
{
    public function issueAccessToken(): void
    {
        $request   = new Request();
        $timestamp = $request->header('X-TIMESTAMP');
        $clientKey = $request->header('X-CLIENT-KEY');
        $signature = $request->header('X-SIGNATURE');

        if (!$timestamp || !$clientKey || !$signature) {
            Response::error('Invalid Mandatory Field', 400, '502');
        }

        if ($clientKey !== Env::get('SNAP_CLIENT_ID')) {
            Response::error('Unauthorized', 401, '401');
        }

        $publicKey = Env::get('SNAP_PUBLIC_KEY');
        $verified  = Signature::verifyAsymmetric($clientKey, $timestamp, $signature, $publicKey);

        if (!$verified) {
            Response::error('Unauthorized. Invalid Signature', 401, '401');
        }

        $accessToken = base64_encode(json_encode([
            'clientId'  => $clientKey,
            'timestamp' => $timestamp,
            'exp'       => time() + 900,
        ]));

        Response::success([
            'accessToken' => $accessToken,
            'tokenType'   => 'BearerToken',
            'expiresIn'   => 900,
        ]);
    }

    public function getAccessToken(): void
    {
        $timestamp  = SnapHelper::timestamp();
        $clientId   = Env::get('SNAP_CLIENT_ID');
        $privateKey = Env::get('SNAP_PRIVATE_KEY');

        $signature = Signature::generateAsymmetric($clientId, $timestamp, $privateKey);

        Response::success([
            'clientId'  => $clientId,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);
    }

    /**
     * Helper — generate symmetric signature untuk test
     * GET /snap/v1.0/get-symmetric-signature
     */
    public function getSymmetricSignature(): void
    {
        $request     = new Request();
        $accessToken = $request->input('accessToken');
        $method      = $request->input('method', 'GET');
        $endpoint    = $request->input('endpoint', '/snap/v1.0/dummy');
        $body        = $request->input('body', '{}');
        $timestamp   = SnapHelper::timestamp();

        if (!$accessToken) {
            Response::error('accessToken is required', 400, '502');
        }

        $signature = Signature::generateSymmetric(
            strtoupper($method),
            $endpoint,
            $accessToken,
            $body,
            $timestamp,
            Env::get('SNAP_CLIENT_SECRET')
        );

        Response::success([
            'method'    => strtoupper($method),
            'endpoint'  => $endpoint,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'howToUse'  => "Use Authorization: Bearer $accessToken with X-TIMESTAMP and X-SIGNATURE headers",
        ]);
    }

    public function dummy(): void
    {
        Response::success([
            'message' => 'SNAP endpoint works!',
        ]);
    }
}