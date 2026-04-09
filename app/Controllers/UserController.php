<?php

namespace App\Controllers;

class UserController
{
    public function index(): void
    {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'responseCode'    => '200M500',
            'responseMessage' => 'Successful',
            'data'            => [
                ['id' => 1, 'name' => 'Budi'],
                ['id' => 2, 'name' => 'Ani'],
            ],
        ]);
    }
}