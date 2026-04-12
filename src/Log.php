<?php

namespace Artemis;

class Log
{
    private static string $logPath = '';

    public static function init(): void
    {
        $dir = dirname(__DIR__, 1) . '/storage/logs';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        self::$logPath = $dir . '/' . date('Y-m-d') . '.log';
    }

    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    public static function warning(string $message): void
    {
        self::write('WARNING', $message);
    }

    public static function request(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? '-';
        $uri    = $_SERVER['REQUEST_URI'] ?? '-';
        $ip     = $_SERVER['REMOTE_ADDR'] ?? '-';

        self::write('REQUEST', "$method $uri from $ip");
    }

    private static function write(string $level, string $message): void
    {
        if (empty(self::$logPath)) {
            self::init();
        }

        $timestamp = date('Y-m-d H:i:s');
        $line      = "[$timestamp] $level: $message" . PHP_EOL;

        file_put_contents(self::$logPath, $line, FILE_APPEND | LOCK_EX);
    }
}