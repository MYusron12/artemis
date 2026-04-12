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

### 3. Setup environment

```bash
cp .env.example .env
```

### 4. Run migrations

```bash
php artemis migrate
```

### 5. Run the server

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
│   ├── QueryBuilder.php
│   ├── Validator.php
│   ├── ErrorHandler.php
│   ├── Env.php
│   └── Log.php
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
├── storage/
│   └── logs/              # Daily log files
│
├── tests/
│   ├── Unit/
│   │   ├── ValidatorTest.php
│   │   ├── RouterTest.php
│   │   ├── RequestTest.php
│   │   └── ResponseTest.php
│   └── Feature/
│       └── UserTest.php
│
├── public/
│   ├── index.php          # Entry point
│   └── index.html         # Landing page
│
├── artemis                # CLI tool
├── phpunit.xml
├── composer.json
└── .env
```

---

## Environment

Create a `.env` file in the root directory:

```env
APP_ENV=development

# SQLite (default)
DB_DRIVER=sqlite
DB_PATH=database/artemis.db

# MySQL
# DB_DRIVER=mysql
# DB_HOST=localhost
# DB_PORT=3306
# DB_NAME=artemis
# DB_USER=root
# DB_PASS=
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
use Artemis\Validator;
use Artemis\Log;

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

        $user = Database::table('users')->where('email', $request->input('email'))->first();

        Log::info('User created: ' . $request->input('email'));

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

## Validation

```php
$validator = Validator::make($request->body(), [
    'name'  => 'required|min:3|max:100',
    'email' => 'required|email',
    'age'   => 'required|numeric',
]);

if ($validator->fails()) {
    Response::error($validator->firstError(), 400, '502');
}
```

Available rules:

| Rule | Description |
|---|---|
| `required` | Field must not be empty |
| `min:N` | Minimum N characters |
| `max:N` | Maximum N characters |
| `email` | Must be a valid email format |
| `numeric` | Must be a number |

---

## Database

Artemis uses SQLite by default, with MySQL support via `.env`. Run migrations with:

```bash
php artemis migrate
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

## Logging

Logs are stored in `storage/logs/` as daily files (e.g. `2026-04-11.log`).

```php
use Artemis\Log;

Log::info('User created: budi@mail.com');
Log::warning('Failed login attempt');
Log::error('Something went wrong');
```

All incoming requests are logged automatically.

Example log output:

```
[2026-04-11 10:00:00] REQUEST: GET /openapi/v1.0/users from ::1
[2026-04-11 10:00:01] REQUEST: POST /openapi/v1.0/users from ::1
[2026-04-11 10:00:01] INFO: User created: citra@mail.com
[2026-04-11 10:00:05] ERROR: UNIQUE constraint failed: users.email
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

**Server Error:**
```json
{
  "responseCode": "500M500",
  "responseMessage": "Internal Server Error"
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

### POST /openapi/v1.0/users

```
POST http://localhost:8300/openapi/v1.0/users
Content-Type: application/json

{
  "name": "Citra",
  "email": "citra@mail.com"
}
```

### GET /openapi/v1.0/users/{id}

```
GET http://localhost:8300/openapi/v1.0/users/1
```

### PUT /openapi/v1.0/users/{id}

```
PUT http://localhost:8300/openapi/v1.0/users/1
Content-Type: application/json

{
  "name": "Doni",
  "email": "doni@mail.com"
}
```

### DELETE /openapi/v1.0/users/{id}

```
DELETE http://localhost:8300/openapi/v1.0/users/1
```

---

## Testing

Artemis uses PHPUnit for testing.

```bash
# Run all tests
php artemis test

# Or directly
vendor/bin/phpunit
```

Test structure:

```
tests/
├── Unit/          # Test individual components
│   ├── ValidatorTest.php
│   ├── RouterTest.php
│   ├── RequestTest.php
│   └── ResponseTest.php
└── Feature/       # Test complete features with database
    └── UserTest.php
```

---

## CLI Tool

```bash
php artemis run                             # Start development server at localhost:8300
php artemis migrate                         # Run database migrations
php artemis test                            # Run unit tests
php artemis make:controller UserController  # Generate a controller
php artemis make:migration create_users     # Generate a migration
```

---

## Roadmap

- [x] CLI Tool
- [x] Router (with group/versioning support)
- [x] Request & Response
- [x] Middleware
- [x] Database / Query Builder (SQLite & MySQL)
- [x] Validation
- [x] Error Handler
- [x] Environment (.env)
- [x] Logging
- [x] Unit Testing
- [x] make:controller & make:migration
- [ ] SNAP BI Support

---

## License

MIT License — free to use, modify, and distribute.