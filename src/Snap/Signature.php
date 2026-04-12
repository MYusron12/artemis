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
}