<?php

namespace Artemis;

class Response
{
    public static function success(mixed $data = null, string $message = 'Successful', int $httpCode = 200): void
    {
        $body = [
            'responseCode'    => $httpCode . 'M500',
            'responseMessage' => $message,
        ];

        if ($data !== null) {
            $body['data'] = $data;
        }

        self::send($body, $httpCode);
    }

    public static function error(string $message, int $httpCode = 400, string $serviceCode = '502'): void
    {
        $body = [
            'responseCode'    => $httpCode . 'M' . $serviceCode,
            'responseMessage' => $message,
        ];

        self::send($body, $httpCode);
    }

    private static function send(array $body, int $httpCode): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode($body);
        exit;
    }
}