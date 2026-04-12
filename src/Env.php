<?php

namespace Artemis;

class Env
{
    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);
        $lines   = explode("\n", $content);
        $i       = 0;

        while ($i < count($lines)) {
            $line = trim($lines[$i]);

            // Skip komentar dan baris kosong
            if (empty($line) || str_starts_with($line, '#')) {
                $i++;
                continue;
            }

            // Cek ada = tidak
            if (!str_contains($line, '=')) {
                $i++;
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Cek apakah value multiline (diawali tanda kutip)
            if (str_starts_with($value, '"') && !str_ends_with($value, '"')) {
                $value = ltrim($value, '"');
                $i++;

                while ($i < count($lines)) {
                    $nextLine = $lines[$i];

                    if (str_ends_with(trim($nextLine), '"')) {
                        $value .= "\n" . rtrim(trim($nextLine), '"');
                        break;
                    }

                    $value .= "\n" . $nextLine;
                    $i++;
                }
            } else {
                $value = trim($value, '"');
            }

            $_ENV[$key]  = $value;
            putenv("$key=$value");
            $i++;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}