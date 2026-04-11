<?php

namespace App\Controllers;

use Artemis\Request;
use Artemis\Response;
use Artemis\Database;

class UserController
{
    public function index(): void
    {
        $users = Database::table('users')->get();
        Response::success($users);
    }

    public function show(string $id): void
    {
        $user = Database::table('users')->where('id', $id)->first();

        if (!$user) {
            Response::error('User Not Found', 404, '503');
        }

        Response::success($user);
    }

    public function store(): void
    {
        $request = new Request();
        $name    = $request->input('name');
        $email   = $request->input('email');

        if (!$name) {
            Response::error('Invalid Mandatory Field name', 400, '502');
        }

        if (!$email) {
            Response::error('Invalid Mandatory Field email', 400, '502');
        }

        Database::table('users')->insert([
            'name'  => $name,
            'email' => $email,
        ]);

        $user = Database::table('users')
            ->where('email', $email)
            ->first();

        Response::success($user, 'Successful', 201);
    }

    public function update(string $id): void
    {
        $request = new Request();
        $name    = $request->input('name');
        $email   = $request->input('email');

        Database::table('users')->where('id', $id)->update([
            'name'  => $name,
            'email' => $email,
        ]);

        $user = Database::table('users')->where('id', $id)->first();
        Response::success($user);
    }

    public function destroy(string $id): void
    {
        Database::table('users')->where('id', $id)->delete();
        Response::success(null, 'Deleted');
    }
}