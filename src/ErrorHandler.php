<?php

namespace Artemis;

class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler(function($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function(\Throwable $e) {
            self::handle($e);
        });

        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
                self::handle(new \ErrorException(
                    $error['message'], 0, $error['type'],
                    $error['file'], $error['line']
                ));
            }
        });
    }

    private static function handle(\Throwable $e): void
    {
        Log::error($e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());

        http_response_code(500);
        header('Content-Type: application/json');

        $response = [
            'responseCode'    => '500M500',
            'responseMessage' => 'Internal Server Error',
        ];

        if (self::isDebug()) {
            $response['debug'] = [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ];
        }

        echo json_encode($response);
        exit;
    }

    private static function isDebug(): bool
    {
        return Env::get('APP_ENV', 'production') === 'development';
    }
}