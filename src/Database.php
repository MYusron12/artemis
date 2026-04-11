<?php

namespace Artemis;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function connect(string $path): void
    {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO('sqlite:' . $path);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
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