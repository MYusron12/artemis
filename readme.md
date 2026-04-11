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
│   └── Database.php
│
├── app/                   # Your application code
│   └── Controllers/
│       └── UserController.php
│
├── database/
│   └── migrations/
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

$router->group('/openapi/v1.0', function($router) {
    $router->get('/users', [UserController::class, 'index']);
    $router->post('/users', [UserController::class, 'store']);
    $router->get('/users/{id}', [UserController::class, 'show']);
    $router->put('/users/{id}', [UserController::class, 'update']);
    $router->delete('/users/{id}', [UserController::class, 'destroy']);
});

// Adding a new version is easy
$router->group('/openapi/v2.0', function($router) {
    $router->get('/users', [UserController::class, 'index']);
});
```

With middleware:

```php
$router->get('/profile', [UserController::class, 'profile'])
       ->middleware(AuthMiddleware::class);
```

---

## Controllers

```php
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

**Not Found:**
```json
{
  "responseCode": "404M503",
  "responseMessage": "Route Not Found"
}
```

---

## API Collection

Base URL: `http://localhost:8300`

### GET /openapi/v1.0/users

Retrieve all users.

```
GET http://localhost:8300/openapi/v1.0/users
```

Response:
```json
{
  "responseCode": "200M500",
  "responseMessage": "Successful",
  "data": [
    { "id": 1, "name": "Budi" },
    { "id": 2, "name": "Ani" }
  ]
}
```

---

### POST /openapi/v1.0/users

Create a new user.

```
POST http://localhost:8300/openapi/v1.0/users
Content-Type: application/json

{
  "name": "Citra"
}
```

Response:
```json
{
  "responseCode": "201M500",
  "responseMessage": "Successful",
  "data": { "id": 3, "name": "Citra" }
}
```

---

### GET /openapi/v1.0/users/{id}

Retrieve a user by ID.

```
GET http://localhost:8300/openapi/v1.0/users/1
```

Response:
```json
{
  "responseCode": "200M500",
  "responseMessage": "Successful",
  "data": { "id": "1", "name": "Budi" }
}
```

---

### PUT /openapi/v1.0/users/{id}

Update a user by ID.

```
PUT http://localhost:8300/openapi/v1.0/users/1
Content-Type: application/json

{
  "name": "Doni"
}
```

Response:
```json
{
  "responseCode": "200M500",
  "responseMessage": "Successful",
  "data": { "id": "1", "name": "Doni" }
}
```

---

### DELETE /openapi/v1.0/users/{id}

Delete a user by ID.

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
php artemis make:controller UserController  # Generate a controller
php artemis migrate                         # Run database migrations
php artemis migrate:rollback                # Rollback last migration
```

---

## Roadmap

- [x] CLI Tool
- [x] Router (with group/versioning support)
- [x] Request & Response
- [ ] Middleware
- [ ] Database / Query Builder
- [ ] Validation
- [ ] Error Handler
- [ ] SNAP BI Support

---

## License

MIT License — free to use, modify, and distribute.