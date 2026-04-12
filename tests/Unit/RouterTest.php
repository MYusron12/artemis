<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Artemis\Router;

class RouterTest extends TestCase
{
    public function test_can_add_get_route(): void
    {
        $router = new Router();
        $router->get('/users', ['UserController', 'index']);

        $routes = $router->getRoutes();

        $this->assertCount(1, $routes);
        $this->assertEquals('GET', $routes[0]['method']);
        $this->assertEquals('/users', $routes[0]['path']);
    }

    public function test_can_add_post_route(): void
    {
        $router = new Router();
        $router->post('/users', ['UserController', 'store']);

        $routes = $router->getRoutes();

        $this->assertEquals('POST', $routes[0]['method']);
    }

    public function test_group_prefix_applied(): void
    {
        $router = new Router();
        $router->group('/openapi/v1.0', function($router) {
            $router->get('/users', ['UserController', 'index']);
        });

        $routes = $router->getRoutes();

        $this->assertEquals('/openapi/v1.0/users', $routes[0]['path']);
    }

    public function test_nested_group_prefix(): void
    {
        $router = new Router();
        $router->group('/openapi', function($router) {
            $router->group('/v1.0', function($router) {
                $router->get('/users', ['UserController', 'index']);
            });
        });

        $routes = $router->getRoutes();

        $this->assertEquals('/openapi/v1.0/users', $routes[0]['path']);
    }
}