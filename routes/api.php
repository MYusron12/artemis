<?php

use App\Controllers\UserController;
use App\Controllers\SnapController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\LogMiddleware;
use Artemis\Snap\SnapMiddleware;
use App\Middlewares\RateLimitMiddleware;

// Middleware per route
$router->group('/openapi/v1.0', function($router) {
    $router->get('/users', [UserController::class, 'index']);
    $router->post('/users', [UserController::class, 'store']);
    $router->get('/users/{id}', [UserController::class, 'show']);
    $router->put('/users/{id}', [UserController::class, 'update']);
    $router->delete('/users/{id}', [UserController::class, 'destroy']);
}, [RateLimitMiddleware::class, LogMiddleware::class]);

// Middleware per group contoh authmidleware dan ratelimitmidleware
$router->group('/openapi/v1.0', function($router) {
    $router->get('/profile', [UserController::class, 'index']);
}, [AuthMiddleware::class, RateLimitMiddleware::class]);

// Helper endpoints untuk testing
$router->get('/snap/v1.0/get-token', [SnapController::class, 'getAccessToken']);
$router->get('/snap/v1.0/get-symmetric-signature', [SnapController::class, 'getSymmetricSignature']);

// Issue access token
$router->post('/snap/v1.0/access-token/b2b', [SnapController::class, 'issueAccessToken'])
       ->middleware(RateLimitMiddleware::class);

// Protected endpoints — butuh symmetric signature
$router->group('/snap/v1.0', function($router) {
    $router->get('/dummy', [SnapController::class, 'dummy']);
}, [SnapMiddleware::class]);