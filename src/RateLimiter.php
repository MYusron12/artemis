<?php

namespace Artemis;

class RateLimiter
{
    private static string $storePath = '';

    public static function init(): void
    {
        $dir = dirname(__DIR__, 1) . '/storage/rate_limiter';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        self::$storePath = $dir;
    }

    public static function check(string $ip, int $maxRequests = 60, int $windowSeconds = 60): bool
    {
        if (empty(self::$storePath)) {
            self::init();
        }

        $file = self::$storePath . '/' . md5($ip) . '.json';
        $now  = time();
        $data = self::load($file);

        // Hapus request yang sudah lewat window
        $data['requests'] = array_filter(
            $data['requests'] ?? [],
            fn($timestamp) => ($now - $timestamp) < $windowSeconds
        );

        // Cek apakah sudah melebihi limit
        if (count($data['requests']) >= $maxRequests) {
            return false;
        }

        // Tambahkan request baru
        $data['requests'][] = $now;
        self::save($file, $data);

        return true;
    }

    public static function remaining(string $ip, int $maxRequests = 60, int $windowSeconds = 60): int
    {
        if (empty(self::$storePath)) {
            self::init();
        }

        $file = self::$storePath . '/' . md5($ip) . '.json';
        $now  = time();
        $data = self::load($file);

        $recent = array_filter(
            $data['requests'] ?? [],
            fn($timestamp) => ($now - $timestamp) < $windowSeconds
        );

        return max(0, $maxRequests - count($recent));
    }

    private static function load(string $file): array
    {
        if (!file_exists($file)) {
            return ['requests' => []];
        }

        return json_decode(file_get_contents($file), true) ?? ['requests' => []];
    }

    private static function save(string $file, array $data): void
    {
        file_put_contents($file, json_encode($data), LOCK_EX);
    }
}