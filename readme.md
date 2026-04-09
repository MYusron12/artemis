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
git clone https://github.com/username/artemis.git
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

Open your browser at `http://localhost:8100`

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
│
├── database/
│   └── migrations/
│
├── routes/
│   └── api.php
│
├── public/
│   └── index.php          # Entry point
│
├── artemis                # CLI tool
└── composer.json
```

---

## Routing

Define your routes in `routes/api.php`:

```php
$router->get('/users', [UserController::class, 'index']);
$router->post('/users', [UserController::class, 'store']);
$router->get('/users/{id}', [UserController::class, 'show']);
$router->put('/users/{id}', [UserController::class, 'update']);
$router->delete('/users/{id}', [UserController::class, 'destroy']);
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
    public function index(Request $request): Response
    {
        $users = []; // fetch from database
        return Response::success($users);
    }

    public function store(Request $request): Response
    {
        $name = $request->input('name');
        // save to database
        return Response::success(null, 'Created', 201);
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

**Error:**
```json
{
  "responseCode": "400M502",
  "responseMessage": "Invalid Mandatory Field"
}
```

---

## CLI Tool

Artemis comes with a built-in CLI tool:

```bash
php artemis run                          # Start development server
php artemis make:controller UserController  # Generate a controller
php artemis migrate                      # Run database migrations
php artemis migrate:rollback             # Rollback last migration
```

---

## Roadmap

- [x] CLI Tool
- [ ] Router
- [ ] Request & Response
- [ ] Middleware
- [ ] Database / Query Builder
- [ ] Validation
- [ ] Error Handler
- [ ] SNAP BI Support

---

## License

MIT License — free to use, modify, and distribute.