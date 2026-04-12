<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Artemis\Database;
use Artemis\Env;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        Env::load(__DIR__ . '/../../.env.testing');
        Database::connect();

        // Buat tabel fresh setiap test
        $pdo = Database::getConnection();
        $pdo->exec("DROP TABLE IF EXISTS users");
        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function test_can_insert_user(): void
    {
        Database::table('users')->insert([
            'name'  => 'Budi',
            'email' => 'budi@mail.com',
        ]);

        $user = Database::table('users')->where('email', 'budi@mail.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('Budi', $user['name']);
    }

    public function test_can_get_all_users(): void
    {
        Database::table('users')->insert(['name' => 'Budi', 'email' => 'budi@mail.com']);
        Database::table('users')->insert(['name' => 'Ani', 'email' => 'ani@mail.com']);

        $users = Database::table('users')->get();

        $this->assertCount(2, $users);
    }

    public function test_can_update_user(): void
    {
        Database::table('users')->insert(['name' => 'Budi', 'email' => 'budi@mail.com']);

        Database::table('users')->where('email', 'budi@mail.com')->update([
            'name' => 'Budi Updated',
        ]);

        $user = Database::table('users')->where('email', 'budi@mail.com')->first();

        $this->assertEquals('Budi Updated', $user['name']);
    }

    public function test_can_delete_user(): void
    {
        Database::table('users')->insert(['name' => 'Budi', 'email' => 'budi@mail.com']);

        Database::table('users')->where('email', 'budi@mail.com')->delete();

        $user = Database::table('users')->where('email', 'budi@mail.com')->first();

        $this->assertNull($user);
    }

    public function test_cannot_insert_duplicate_email(): void
    {
        $this->expectException(\RuntimeException::class);

        Database::table('users')->insert(['name' => 'Budi', 'email' => 'budi@mail.com']);
        Database::table('users')->insert(['name' => 'Budi 2', 'email' => 'budi@mail.com']);
    }
}