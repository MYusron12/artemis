<?php

use App\Controllers\UserController;
use App\Controllers\SnapController;
use App\Middlewares\AuthMiddleware;
use Artemis\Snap\SnapMiddleware;

// Middleware per route
$router->group('/openapi/v1.0', function($router) {
    $router->get('/users', [UserController::class, 'index']);
    $router->post('/users', [UserController::class, 'store']);
    $router->get('/users/{id}', [UserController::class, 'show']);
    $router->put('/users/{id}', [UserController::class, 'update']);
    $router->delete('/users/{id}', [UserController::class, 'destroy']);
});

// Middleware per group
$router->group('/openapi/v1.0', function($router) {
    $router->get('/profile', [UserController::class, 'index']);
}, [AuthMiddleware::class]);

// Middleware per route saja
$router->get('/openapi/v1.0/secret', [UserController::class, 'index'])
       ->middleware(AuthMiddleware::class);

// Endpoint untuk issue access token (tanpa middleware dulu)
$router->post('/snap/v1.0/access-token/b2b', [SnapController::class, 'issueAccessToken']);

// Endpoint yang butuh access token
$router->group('/snap/v1.0', function($router) {
    $router->get('/dummy', [SnapController::class, 'dummy']);
}, [SnapMiddleware::class]);

// Helper — generate signature untuk test
$router->get('/snap/v1.0/get-token', [SnapController::class, 'getAccessToken']);