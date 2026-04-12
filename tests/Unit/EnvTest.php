<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Artemis\Env;

class EnvTest extends TestCase
{
    public function test_get_default_value(): void
    {
        $value = Env::get('KEY_TIDAK_ADA', 'default');
        $this->assertEquals('default', $value);
    }

    public function test_get_env_value(): void
    {
        $_ENV['APP_NAME'] = 'Artemis';
        $value = Env::get('APP_NAME');
        $this->assertEquals('Artemis', $value);
    }
}