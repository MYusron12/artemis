<?php

namespace App\Controllers;

use Artemis\Request;
use Artemis\Response;

class UserController
{
    public function index(): void
    {
        Response::success([
            ['id' => 1, 'name' => 'Budi'],
            ['id' => 2, 'name' => 'Ani'],
        ]);
    }

    public function show(string $id): void
    {
        Response::success(['id' => $id, 'name' => 'Budi']);
    }

    public function store(): void
    {
        $request = new Request();
        $name    = $request->input('name');

        if (!$name) {
            Response::error('Invalid Mandatory Field name', 400, '502');
        }

        Response::success(['id' => 3, 'name' => $name], 'Successful', 201);
    }

    public function update(string $id): void
    {
        $request = new Request();
        $name    = $request->input('name');

        Response::success(['id' => $id, 'name' => $name]);
    }

    public function destroy(string $id): void
    {
        Response::success(null, 'Deleted');
    }
}