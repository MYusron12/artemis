<?php

use App\Controllers\UserController;

$router->get('/users', [UserController::class, 'index']);