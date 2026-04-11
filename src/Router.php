<?php

namespace Artemis;

class Router
{
    private array $routes = [];
    private string $prefix = '';

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix  = $this->prefix;
        $this->prefix   .= $prefix;

        $callback($this);

        $this->prefix = $previousPrefix;
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'path'    => $this->prefix . $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            $params = [];

            if ($route['method'] !== $method) {
                continue;
            }

            if ($this->match($route['path'], $uri, $params)) {
                [$class, $action] = $route['handler'];
                $controller = new $class();
                $controller->$action(...$params);
                return;
            }
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'responseCode'    => '404M503',
            'responseMessage' => 'Route Not Found',
        ]);
    }

    private function match(string $routePath, string $uri, array &$params): bool
    {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            $params = $matches;
            return true;
        }

        return false;
    }
}