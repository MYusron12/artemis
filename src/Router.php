<?php

namespace Artemis;

class Router
{
    private array $routes = [];
    private string $prefix = '';
    private array $groupMiddlewares = [];

    public function get(string $path, array $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, array $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    public function group(string $prefix, callable $callback, array $middlewares = []): void
    {
        $previousPrefix      = $this->prefix;
        $previousMiddlewares = $this->groupMiddlewares;

        $this->prefix           .= $prefix;
        $this->groupMiddlewares  = array_merge($this->groupMiddlewares, $middlewares);

        $callback($this);

        $this->prefix           = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;
    }

    public function middleware(string $middleware): self
    {
        $last = array_key_last($this->routes);
        $this->routes[$last]['middlewares'][] = $middleware;
        return $this;
    }

    private function addRoute(string $method, string $path, array $handler): self
    {
        $this->routes[] = [
            'method'      => $method,
            'path'        => $this->prefix . $path,
            'handler'     => $handler,
            'middlewares' => $this->groupMiddlewares,
        ];
        return $this;
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
                $request = new Request();

                $this->runMiddlewares(
                    $route['middlewares'],
                    $request,
                    function() use ($route, $request, $params) {
                        [$class, $action] = $route['handler'];
                        $controller = new $class();
                        $controller->$action(...$params);
                    }
                );
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

    private function runMiddlewares(array $middlewares, Request $request, callable $final): void
    {
        if (empty($middlewares)) {
            $final();
            return;
        }

        $chain = array_reduce(
            array_reverse($middlewares),
            function($carry, $middlewareClass) use ($request) {
                return function() use ($middlewareClass, $request, $carry) {
                    (new $middlewareClass())->handle($request, $carry);
                };
            },
            $final
        );

        $chain();
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

    public function getRoutes(): array
    {
        return $this->routes;
    }
}