<?php

namespace Artemis;

class Request
{
    public function input(string $key, mixed $default = null): mixed
    {
        $body = $this->body();
        return $body[$key] ?? $_GET[$key] ?? $default;
    }

    public function body(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?? [];
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function header(string $key): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$key] ?? null;
    }
}