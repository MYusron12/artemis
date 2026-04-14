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
- OpenSSL extension (for SNAP BI)

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

### 3. Generate RSA keys (for SNAP BI)

```bash
php generate_keys.php
```

Or manually via OpenSSL CLI:

```bash
# Generate private key
openssl genrsa -out private_key.pem 2048

# Extract public key from private key
openssl rsa -in private_key.pem -pubout -out public_key.pem
```

After generating, copy the key contents into your `.env` file (see Environment section).

> Add `private_key.pem` and `public_key.pem` to `.gitignore` — never commit these files.

### 4. Setup environment

```bash
cp .env.example .env
```

### 5. Run migrations

```bash
php artemis migrate
```

### 6. Run the server

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
│   ├── RateLimiter.php
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
│       ├── AuthMiddleware.php
│       ├── CorsMiddleware.php
│       ├── RateLimitMiddleware.php
│       └── LogMiddleware.php
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
│   ├── logs/              # Daily log files
│   └── rate_limiter/      # Rate limiter store
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
├── generate_keys.php      # RSA key generator
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

# CORS
CORS_ORIGIN=*
# CORS_ORIGIN=http://192.168.1.100,https://myapp.com

# Rate Limiter
RATE_LIMIT_MAX=60
RATE_LIMIT_WINDOW=60

# SNAP BI
SNAP_CLIENT_ID=your-client-id
SNAP_CLIENT_SECRET=your-client-secret
SNAP_BASE_URL=https://api.bank.co.id
SNAP_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----
...isi private key...
-----END PRIVATE KEY-----"
SNAP_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----
...isi public key...
-----END PUBLIC KEY-----"
```

---

## Generating RSA Keys

Artemis uses RSA 2048-bit keys for SNAP BI Asymmetric Signature.

### Option 1 — PHP script (recommended)

Create `generate_keys.php` in root:

```php
<?php

putenv('OPENSSL_CONF=C:/xampp/apache/conf/openssl.cnf');

$config = [
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'config'           => 'C:/xampp/apache/conf/openssl.cnf',
];

$res = openssl_pkey_new($config);
openssl_pkey_export($res, $privateKey, null, $config);
$publicKey = openssl_pkey_get_details($res)['key'];

file_put_contents('private_key.pem', $privateKey);
file_put_contents('public_key.pem', $publicKey);

echo "private_key.pem — OK\n";
echo "public_key.pem  — OK\n";
```

Run:

```bash
php generate_keys.php
```

### Option 2 — OpenSSL CLI

```bash
# Generate private key
openssl genrsa -out private_key.pem 2048

# Extract public key
openssl rsa -in private_key.pem -pubout -out public_key.pem
```

### Option 3 — Online generator (development only)

Use [https://cryptotools.net/rsagen](https://cryptotools.net/rsagen) — set key size to 2048.

> Never use online generators for production keys.

### Copy keys to .env

After generating, open `private_key.pem` and `public_key.pem`, copy the full content including headers into `.env`:

```env
SNAP_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----
MIIEvAIBADANBgkqhkiG9w0BAQEFAASC...
-----END PRIVATE KEY-----"

SNAP_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A...
-----END PUBLIC KEY-----"
```

---

## Routing

```php
<?php

use App\Controllers\UserController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\RateLimitMiddleware;
use App\Middlewares\LogMiddleware;

$router->group('/openapi/v1.0', function($router) {
    $router->get('/users', [UserController::class, 'index']);
    $router->post('/users', [UserController::class, 'store']);
    $router->get('/users/{id}', [UserController::class, 'show']);
    $router->put('/users/{id}', [UserController::class, 'update']);
    $router->delete('/users/{id}', [UserController::class, 'destroy']);
}, [RateLimitMiddleware::class, LogMiddleware::class]);

// Multiple middleware
$router->group('/openapi/v1.0', function($router) {
    $router->get('/profile', [UserController::class, 'index']);
}, [AuthMiddleware::class, RateLimitMiddleware::class]);

// Per route
$router->get('/openapi/v1.0/secret', [UserController::class, 'index'])
       ->middleware(AuthMiddleware::class);
```

---

## Middleware

Available middlewares:

| Middleware | Description |
|---|---|
| `AuthMiddleware` | Validate Bearer token |
| `RateLimitMiddleware` | Limit requests per IP |
| `LogMiddleware` | Log request start and duration |
| `SnapMiddleware` | Validate SNAP BI symmetric signature |

Generate a new middleware:

```bash
php artemis make:middleware NamaMiddleware
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

Artemis supports SQLite and MySQL. Run migrations:

```bash
php artemis migrate
```

Query Builder:

```php
Database::table('users')->get();
Database::table('users')->where('id', 1)->first();
Database::table('users')->insert(['name' => 'Budi', 'email' => 'budi@mail.com']);
Database::table('users')->where('id', 1)->update(['name' => 'Ani']);
Database::table('users')->where('id', 1)->delete();
```

---

## Logging

```php
use Artemis\Log;

Log::info('User created: budi@mail.com');
Log::warning('Failed login attempt');
Log::error('Something went wrong');
```

Log output example:

```
[2026-04-12 10:00:00] REQUEST: GET /openapi/v1.0/users from ::1
[2026-04-12 10:00:00] INFO: START GET /openapi/v1.0/users from ::1
[2026-04-12 10:00:00] INFO: END GET /openapi/v1.0/users — 3.42ms
[2026-04-12 10:00:01] INFO: User created: citra@mail.com
```

---

## SNAP BI

### Step 1 — Generate signature

```
GET http://localhost:8300/snap/v1.0/get-token
```

### Step 2 — Request access token

```
POST http://localhost:8300/snap/v1.0/access-token/b2b
X-CLIENT-KEY: your-client-id
X-TIMESTAMP: 2026-04-12T07:53:29+07:00
X-SIGNATURE: {signature-from-step-1}

{ "grantType": "client_credentials" }
```

### Step 3 — Generate symmetric signature

```
GET http://localhost:8300/snap/v1.0/get-symmetric-signature
  ?accessToken={token}
  &method=GET
  &endpoint=/snap/v1.0/dummy
```

### Step 4 — Hit protected endpoint

```
GET http://localhost:8300/snap/v1.0/dummy
Authorization: Bearer {accessToken}
X-CLIENT-KEY: your-client-id
X-TIMESTAMP: {timestamp-from-step-3}
X-SIGNATURE: {signature-from-step-3}
```

---

## Response Format

| HTTP | Code | Message |
|---|---|---|
| 200 | 200M500 | Successful |
| 201 | 201M500 | Successful |
| 400 | 400M502 | Invalid Mandatory Field |
| 401 | 401M401 | Unauthorized |
| 403 | 403M403 | Forbidden |
| 404 | 404M503 | Route Not Found |
| 409 | 409M509 | Data already exists |
| 429 | 429M429 | Too Many Requests |
| 500 | 500M500 | Internal Server Error |

---

## API Collection

Base URL: `http://localhost:8300`

### Users

```bash
# Get all users
curl -X GET http://localhost:8300/openapi/v1.0/users \
  -H "Content-Type: application/json"

# Create user
curl -X POST http://localhost:8300/openapi/v1.0/users \
  -H "Content-Type: application/json" \
  -d '{"name": "Budi", "email": "budi@mail.com"}'

# Get user by ID
curl -X GET http://localhost:8300/openapi/v1.0/users/1 \
  -H "Content-Type: application/json"

# Update user
curl -X PUT http://localhost:8300/openapi/v1.0/users/1 \
  -H "Content-Type: application/json" \
  -d '{"name": "Doni", "email": "doni@mail.com"}'

# Delete user
curl -X DELETE http://localhost:8300/openapi/v1.0/users/1 \
  -H "Content-Type: application/json"
```

### SNAP BI

```bash
# Step 1 — Generate asymmetric signature
curl -X GET http://localhost:8300/snap/v1.0/get-token

# Step 2 — Request access token
curl -X POST http://localhost:8300/snap/v1.0/access-token/b2b \
  -H "Content-Type: application/json" \
  -H "X-CLIENT-KEY: artemis-dummy-client-001" \
  -H "X-TIMESTAMP: 2026-04-12T07:53:29+07:00" \
  -H "X-SIGNATURE: {signature}" \
  -d '{"grantType": "client_credentials"}'

# Step 3 — Generate symmetric signature
curl -X GET "http://localhost:8300/snap/v1.0/get-symmetric-signature?accessToken={token}&method=GET&endpoint=/snap/v1.0/dummy"

# Step 4 — Hit protected endpoint
curl -X GET http://localhost:8300/snap/v1.0/dummy \
  -H "Authorization: Bearer {accessToken}" \
  -H "X-CLIENT-KEY: artemis-dummy-client-001" \
  -H "X-TIMESTAMP: {timestamp}" \
  -H "X-SIGNATURE: {signature}"
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
php artemis make:middleware AuthMiddleware  # Generate a middleware
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
- [x] make:controller, make:migration & make:middleware
- [x] SNAP BI Support (Asymmetric & Symmetric Signature)
- [x] CORS
- [x] Rate Limiter

---

## Support

If you find this project helpful, consider supporting:

- Instagram: [@m_yussron](https://www.instagram.com/m_yussron/?hl=en)
- Trakteer: [trakteer.id/muhammad_yusron17](https://trakteer.id/muhammad_yusron17)

---

## License

MIT License — free to use, modify, and distribute.