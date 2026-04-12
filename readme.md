# Artemis

> A simple, lightweight PHP backend framework.

<p>
  <a href="https://www.instagram.com/m_yussron/?hl=en" target="_blank">
    <img src="https://img.shields.io/badge/Instagram-@m__yussron-E4405F?style=flat&logo=instagram&logoColor=white" />
  </a>
  &nbsp;
  <a href="https://trakteer.id/muhammad_yusron17" target="_blank">
    <img src="https://img.shields.io/badge/Trakteer-Support-red?style=flat&logo=ko-fi&logoColor=white" />
  </a>
</p>

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
│   ├── Log.php
│   └── Snap/
│       ├── Signature.php
│       ├── AccessToken.php
│       ├── SnapMiddleware.php
│       └── SnapHelper.php
│
├── app/                   # Your application code
│   ├── Controllers/
│   │   ├── UserController.php
│   │   └── SnapController.php
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

# SNAP BI
SNAP_CLIENT_ID=your-client-id
SNAP_CLIENT_SECRET=your-client-secret
SNAP_BASE_URL=https://api.bank.co.id
SNAP_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----
...
-----END PRIVATE KEY-----"
SNAP_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----
...
-----END PUBLIC KEY-----"
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

Artemis supports SQLite and MySQL via `.env`. Run migrations with:

```bash
php artemis migrate
```

Query Builder usage:

```php
Database::table('users')->get();
Database::table('users')->where('id', 1)->first();
Database::table('users')->insert(['name' => 'Budi', 'email' => 'budi@mail.com']);
Database::table('users')->where('id', 1)->update(['name' => 'Ani']);
Database::table('users')->where('id', 1)->delete();
```

---

## Logging

Logs are stored in `storage/logs/` as daily files.

```php
use Artemis\Log;

Log::info('User created: budi@mail.com');
Log::warning('Failed login attempt');
Log::error('Something went wrong');
```

All incoming requests are logged automatically.

---

## SNAP BI

Artemis has built-in SNAP BI support for Access Token B2B.

### Generate signature & request access token

```php
use Artemis\Snap\Signature;
use Artemis\Snap\SnapHelper;

$timestamp  = SnapHelper::timestamp();
$clientId   = Env::get('SNAP_CLIENT_ID');
$privateKey = Env::get('SNAP_PRIVATE_KEY');

$signature = Signature::generateAsymmetric($clientId, $timestamp, $privateKey);
```

### Routes

```php
use App\Controllers\SnapController;
use Artemis\Snap\SnapMiddleware;

// Issue access token
$router->post('/snap/v1.0/access-token/b2b', [SnapController::class, 'issueAccessToken']);

// Protected endpoint
$router->group('/snap/v1.0', function($router) {
    $router->get('/dummy', [SnapController::class, 'dummy']);
}, [SnapMiddleware::class]);
```

### Access Token Response

```json
{
  "responseCode": "200M500",
  "responseMessage": "Successful",
  "data": {
    "accessToken": "eyJ...",
    "tokenType": "BearerToken",
    "expiresIn": 900
  }
}
```

---

## Response Format

| HTTP | Code | Message |
|---|---|---|
| 200 | 200M500 | Successful |
| 201 | 201M500 | Successful |
| 400 | 400M502 | Invalid Mandatory Field |
| 401 | 401M401 | Unauthorized |
| 404 | 404M503 | Route Not Found |
| 409 | 409M509 | Data already exists |
| 500 | 500M500 | Internal Server Error |

---

## API Collection

Base URL: `http://localhost:8300`

### GET /openapi/v1.0/users
```
GET http://localhost:8300/openapi/v1.0/users
```

### POST /openapi/v1.0/users
```
POST http://localhost:8300/openapi/v1.0/users
Content-Type: application/json

{ "name": "Citra", "email": "citra@mail.com" }
```

### GET /openapi/v1.0/users/{id}
```
GET http://localhost:8300/openapi/v1.0/users/1
```

### PUT /openapi/v1.0/users/{id}
```
PUT http://localhost:8300/openapi/v1.0/users/1
Content-Type: application/json

{ "name": "Doni", "email": "doni@mail.com" }
```

### DELETE /openapi/v1.0/users/{id}
```
DELETE http://localhost:8300/openapi/v1.0/users/1
```

### POST /snap/v1.0/access-token/b2b
```
POST http://localhost:8300/snap/v1.0/access-token/b2b
Content-Type: application/json
X-CLIENT-KEY: artemis-dummy-client-001
X-TIMESTAMP: 2026-04-12T07:53:29+02:00
X-SIGNATURE: your-signature
```

---

## Testing

```bash
php artemis test
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
- [x] SNAP BI Support

---

## Support

If you find this project helpful, consider supporting via:

- Instagram: [@m_yussron](https://www.instagram.com/m_yussron/?hl=en)
- Trakteer: [trakteer.id/muhammad_yusron17](https://trakteer.id/muhammad_yusron17)

---

## License

MIT License — free to use, modify, and distribute.