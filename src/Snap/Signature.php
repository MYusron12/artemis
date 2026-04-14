<?php

namespace Artemis\Snap;

class Signature
{
    /**
     * Generate Asymmetric Signature untuk Access Token B2B
     * Algoritma: SHA256withRSA
     */
    public static function generateAsymmetric(
        string $clientId,
        string $timestamp,
        string $privateKey
    ): string {
        $stringToSign = $clientId . '|' . $timestamp;

        $privateKeyResource = openssl_pkey_get_private($privateKey);

        if (!$privateKeyResource) {
            throw new \RuntimeException('Invalid private key');
        }

        openssl_sign($stringToSign, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }

    /**
     * Generate Symmetric Signature untuk request API
     * Algoritma: HMAC-SHA512
     */
    public static function generateSymmetric(
        string $method,
        string $endpoint,
        string $accessToken,
        string $body,
        string $timestamp,
        string $clientSecret
    ): string {
        $bodyHash     = strtolower(hash('sha256', $body));
        $stringToSign = $method . ':' . $endpoint . ':' . $accessToken . ':' . $bodyHash . ':' . $timestamp;

        return base64_encode(hash_hmac('sha512', $stringToSign, $clientSecret, true));
    }

    public static function verifyAsymmetric(
        string $clientId,
        string $timestamp,
        string $signature,
        string $publicKey
    ): bool {
        $stringToSign = $clientId . '|' . $timestamp;
        $publicKey    = self::formatPublicKey($publicKey);
        $publicRes    = openssl_pkey_get_public($publicKey);
        $decoded      = base64_decode($signature);

        return openssl_verify($stringToSign, $decoded, $publicRes, OPENSSL_ALGO_SHA256) === 1;
    }

    private static function formatPrivateKey(string $key): string
    {
        $key = trim($key);

        if (str_contains($key, '-----BEGIN')) {
            return $key;
        }

        return "-----BEGIN PRIVATE KEY-----\n" .
            chunk_split($key, 64, "\n") .
            "-----END PRIVATE KEY-----\n";
    }

    private static function formatPublicKey(string $key): string
    {
        $key = trim($key);

        if (str_contains($key, '-----BEGIN')) {
            return $key;
        }

        return "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split($key, 64, "\n") .
            "-----END PUBLIC KEY-----\n";
    }
}