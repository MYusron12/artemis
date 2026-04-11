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