<?php

namespace Artemis\Snap;

use Artemis\Env;

class AccessToken
{
    public static function requestB2B(string $timestamp): array
    {
        $clientId   = Env::get('SNAP_CLIENT_ID');
        $privateKey = file_get_contents(dirname(__DIR__, 2) . '/private_key.pem');
        $baseUrl    = Env::get('SNAP_BASE_URL');

        $signature = Signature::generateAsymmetric($clientId, $timestamp, $privateKey);

        $payload = json_encode([
            'grantType' => 'client_credentials',
        ]);

        $headers = [
            'Content-Type: application/json',
            'X-CLIENT-KEY: ' . $clientId,
            'X-TIMESTAMP: ' . $timestamp,
            'X-SIGNATURE: ' . $signature,
        ];

        return self::post($baseUrl . '/snap/v1.0/access-token/b2b', $payload, $headers);
    }

    private static function post(string $url, string $payload, array $headers): array
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [
            'httpCode' => $httpCode,
            'body'     => json_decode($response, true),
        ];
    }
}