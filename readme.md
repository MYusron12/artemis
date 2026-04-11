# Artemis

> A simple, lightweight PHP backend framework.

---

## Philosophy

Artemis is built with one goal: **simplicity**. No magic, no bloat — just the essential building blocks to create a backend API quickly and clearly.

- Lightweight — minimal dependencies
- Readable — code anyone can understand
- Flexible — suitable for general REST APIs
- Extensible — easy to grow as your project grows

---

## Requirements

- PHP >= 8.0
- Composer

---

## Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/MYusron12/artemis.git
cd artemis
```

### 2. Install dependencies

```bash
composer install
```

### 3. Run the server

```bash
php artemis run
```

Open your browser at `http://localhost:8300`

---

## Project Structure

```
artemis/
├── src/                   # Core framework
│   ├── Router.php
│   ├── Request.php
│   ├── Response.php
│   ├── Middleware.php
│   ├── Database.php
│   └── QueryBuilder.php
│
├── app/                   # Your application code
│   ├── Controllers/
│   │   └── UserController.php
│   └── Middlewares/
│       └── AuthMiddleware.php
│
├── database/
│   ├── migrations/
│   │   └── create_users_table.php
│   └── artemis.db
│
├── routes/
│   └── api.php
│
├── public/
│   ├── index.php          # Entry point
│   └── index.html         # Landing page
│
├── artemis                # CLI tool
└── composer.json
```

---

## Routing

Define your routes in `routes/api.php`. Artemis supports route grouping for versioned APIs:

```php
<?php

use App\Controllers\UserController;
use App\Middlewares\AuthMiddleware;

$router->group('/openapi/v1.0', function($router) {
    $router->get('/users', [UserController::class, 'index']);
    $router->post('/users', [UserController::class, 'store']);
    $router->get('/users/{id}', [UserController::class, 'show']);
    $router->put('/users/{id}', [UserController::class, 'update']);
    $router->delete('/users/{id}', [UserController::class, 'destroy']);
});

// With middleware per group
$router->group('/openapi/v1.0', function($router) {
    $router->get('/profile', [UserController::class, 'index']);
}, [AuthMiddleware::class]);

// With middleware per route
$router->get('/openapi/v1.0/secret', [UserController::class, 'index'])
       ->middleware(AuthMiddleware::class);

// Adding a new version is easy
$router->group('/openapi/v2.0', function($router) {
    $router->get('/users', [UserController::class, 'index']);
});
```

---

## Controllers

```php
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

        $user = Database::table('users')->where('email', $email)->first();
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
```

---

## Middleware

Create a middleware in `app/Middlewares/`:

```php
<?php

namespace App\Middlewares;

use Artemis\Middleware;
use Artemis\Request;
use Artemis\Response;

class AuthMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): void
    {
        $token = $request->header('Authorization');

        if (!$token || $token !== 'Bearer secret-token') {
            Response::error('Unauthorized', 401, '401');
        }

        $next();
    }
}
```

---

## Database

Artemis uses SQLite by default. Run migrations with:

```bash
php artemis migrate
```

Create a migration in `database/migrations/`:

```php
<?php

use Artemis\Database;

Database::connect(__DIR__ . '/../../database/artemis.db');

$pdo = Artemis\Database::getConnection();

$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

echo "Migration: users table created.\n";
```

Query Builder usage:

```php
// Get all
Database::table('users')->get();

// Get one
Database::table('users')->where('id', 1)->first();

// Insert
Database::table('users')->insert(['name' => 'Budi', 'email' => 'budi@mail.com']);

// Update
Database::table('users')->where('id', 1)->update(['name' => 'Ani']);

// Delete
Database::table('users')->where('id', 1)->delete();
```

---

## Response Format

All responses follow a consistent JSON structure:

**Success:**
```json
{
  "responseCode": "200M500",
  "responseMessage": "Successful",
  "data": []
}
```

**Created:**
```json
{
  "responseCode": "201M500",
  "responseMessage": "Successful",
  "data": {}
}
```

**Error:**
```json
{
  "responseCode": "400M502",
  "responseMessage": "Invalid Mandatory Field name"
}
```

**Unauthorized:**
```json
{
  "responseCode": "401M401",
  "responseMessage": "Unauthorized"
}
```

**Not Found:**
```json
{
  "responseCode": "404M503",
  "responseMessage": "Route Not Found"
}
```

**Duplicate:**
```json
{
  "responseCode": "409M509",
  "responseMessage": "Data already exists"
}
```

---

## API Collection

Base URL: `http://localhost:8300`

### GET /openapi/v1.0/users

```
GET http://localhost:8300/openapi/v1.0/users
```

Response:
```json
{
  "responseCode": "200M500",
  "responseMessage": "Successful",
  "data": [
    { "id": 1, "name": "Budi", "email": "budi@mail.com", "created_at": "2026-04-11 10:00:00" }
  ]
}
```

---

### POST /openapi/v1.0/users

```
POST http://localhost:8300/openapi/v1.0/users
Content-Type: application/json

{
  "name": "Citra",
  "email": "citra@mail.com"
}
```

Response:
```json
{
  "responseCode": "201M500",
  "responseMessage": "Successful",
  "data": { "id": 1, "name": "Citra", "email": "citra@mail.com", "created_at": "2026-04-11 10:00:00" }
}
```

---

### GET /openapi/v1.0/users/{id}

```
GET http://localhost:8300/openapi/v1.0/users/1
```

Response:
```json
{
  "responseCode": "200M500",
  "responseMessage": "Successful",
  "data": { "id": 1, "name": "Citra", "email": "citra@mail.com", "created_at": "2026-04-11 10:00:00" }
}
```

---

### PUT /openapi/v1.0/users/{id}

```
PUT http://localhost:8300/openapi/v1.0/users/1
Content-Type: application/json

{
  "name": "Doni",
  "email": "doni@mail.com"
}
```

Response:
```json
{
  "responseCode": "200M500",
  "responseMessage": "Successful",
  "data": { "id": 1, "name": "Doni", "email": "doni@mail.com", "created_at": "2026-04-11 10:00:00" }
}
```

---

### DELETE /openapi/v1.0/users/{id}

```
DELETE http://localhost:8300/openapi/v1.0/users/1
```

Response:
```json
{
  "responseCode": "200M500",
  "responseMessage": "Deleted"
}
```

---

## CLI Tool

Artemis comes with a built-in CLI tool:

```bash
php artemis run                             # Start development server at localhost:8300
php artemis migrate                         # Run database migrations
php artemis make:controller UserController  # Generate a controller (coming soon)
php artemis make:migration create_users     # Generate a migration (coming soon)
```

---

## Roadmap

- [x] CLI Tool
- [x] Router (with group/versioning support)
- [x] Request & Response
- [x] Middleware
- [x] Database / Query Builder (SQLite)
- [ ] Validation
- [ ] Error Handler
- [ ] make:controller & make:migration
- [ ] SNAP BI Support

---

## License

MIT License — free to use, modify, and distribute.