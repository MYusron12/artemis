<?php

namespace App\Controllers;

use Artemis\Request;
use Artemis\Response;
use Artemis\Env;
use Artemis\Snap\Signature;
use Artemis\Snap\SnapHelper;

class SnapController
{
    /**
     * Dummy endpoint — issue access token
     * POST /snap/v1.0/access-token/b2b
     */
    public function issueAccessToken(): void
    {
        $request   = new Request();
        $timestamp = $request->header('X-TIMESTAMP');
        $clientKey = $request->header('X-CLIENT-KEY');
        $signature = $request->header('X-SIGNATURE');

        // Validasi header wajib
        if (!$timestamp || !$clientKey || !$signature) {
            Response::error('Invalid Mandatory Field', 400, '502');
        }

        // Validasi client key
        if ($clientKey !== Env::get('SNAP_CLIENT_ID')) {
            Response::error('Unauthorized', 401, '401');
        }

        // Verifikasi signature
        $publicKey    = file_get_contents(dirname(__DIR__, 2) . '/public_key.pem');
        $stringToSign = $clientKey . '|' . $timestamp;

        $decoded   = base64_decode($signature);
        $publicRes = openssl_pkey_get_public($publicKey);
        $verified  = openssl_verify($stringToSign, $decoded, $publicRes, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            Response::error('Unauthorized. Invalid Signature', 401, '401');
        }

        // Generate dummy access token
        $accessToken = base64_encode(json_encode([
            'clientId'  => $clientKey,
            'timestamp' => $timestamp,
            'exp'       => time() + 900, // 15 menit
        ]));

        Response::success([
            'accessToken' => $accessToken,
            'tokenType'   => 'BearerToken',
            'expiresIn'   => 900,
        ]);
    }

    /**
     * Dummy endpoint yang butuh access token
     * GET /snap/v1.0/dummy
     */
    public function dummy(): void
    {
        Response::success([
            'message' => 'SNAP endpoint works!',
        ]);
    }

    /**
     * Client — request access token ke dummy server
     * GET /snap/v1.0/get-token (untuk test)
     */
    public function getAccessToken(): void
    {
        $timestamp  = SnapHelper::timestamp();
        $clientId   = Env::get('SNAP_CLIENT_ID');
        $privateKey = file_get_contents(dirname(__DIR__, 2) . '/private_key.pem');

        $signature = Signature::generateAsymmetric($clientId, $timestamp, $privateKey);

        Response::success([
            'clientId'  => $clientId,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'howToUse'  => 'Copy signature above, then POST to /snap/v1.0/access-token/b2b with headers X-CLIENT-KEY, X-TIMESTAMP, X-SIGNATURE',
        ]);
    }
}