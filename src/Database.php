<?php

namespace Artemis;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function connect(): void
    {
        if (self::$connection !== null) {
            return;
        }

        $driver = Env::get('DB_DRIVER', 'sqlite');

        try {
            if ($driver === 'sqlite') {
                $path = Env::get('DB_PATH', 'database/artemis.db');
                $fullPath = dirname(__DIR__, 1) . '/' . $path;
                self::$connection = new PDO('sqlite:' . $fullPath);
            } elseif ($driver === 'mysql') {
                $host = Env::get('DB_HOST', 'localhost');
                $port = Env::get('DB_PORT', '3306');
                $name = Env::get('DB_NAME', '');
                $user = Env::get('DB_USER', 'root');
                $pass = Env::get('DB_PASS', '');

                $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
                self::$connection = new PDO($dsn, $user, $pass);
            } else {
                die("Database driver '$driver' tidak didukung.\n");
            }

            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getConnection(): PDO
    {
        return self::$connection;
    }

    public static function table(string $table): QueryBuilder
    {
        return new QueryBuilder(self::$connection, $table);
    }
}