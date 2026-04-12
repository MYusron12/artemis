<?php

namespace App\Controllers;

use Artemis\Request;
use Artemis\Response;
use Artemis\Database;
use Artemis\Validator;

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
        $request   = new Request();
        $validator = Validator::make($request->body(), [
            'name'  => 'required|min:3|max:100',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            Response::error($validator->firstError(), 400, '502');
        }

        try {
            Database::table('users')->insert([
                'name'  => $request->input('name'),
                'email' => $request->input('email'),
            ]);
        } catch (\RuntimeException $e) {
            if ($e->getCode() === 409) {
                Response::error('Data already exists', 409, '509');
            }
            Response::error('Database error', 500, '500');
        }

        $user = Database::table('users')
            ->where('email', $request->input('email'))
            ->first();

        Response::success($user, 'Successful', 201);
    }

    public function update(string $id): void
    {
        $request   = new Request();
        $validator = Validator::make($request->body(), [
            'name'  => 'required|min:3|max:100',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            Response::error($validator->firstError(), 400, '502');
        }

        Database::table('users')->where('id', $id)->update([
            'name'  => $request->input('name'),
            'email' => $request->input('email'),
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