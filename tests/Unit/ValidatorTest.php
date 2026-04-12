<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Artemis\Validator;

class ValidatorTest extends TestCase
{
    public function test_passes_when_all_rules_valid(): void
    {
        $validator = Validator::make([
            'name'  => 'Budi',
            'email' => 'budi@mail.com',
        ], [
            'name'  => 'required|min:3|max:100',
            'email' => 'required|email',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_fails_when_required_field_empty(): void
    {
        $validator = Validator::make([], [
            'name' => 'required',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors());
    }

    public function test_fails_when_email_invalid(): void
    {
        $validator = Validator::make([
            'email' => 'bukan-email',
        ], [
            'email' => 'required|email',
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_fails_when_min_not_met(): void
    {
        $validator = Validator::make([
            'name' => 'AB',
        ], [
            'name' => 'required|min:3',
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_fails_when_max_exceeded(): void
    {
        $validator = Validator::make([
            'name' => str_repeat('A', 101),
        ], [
            'name' => 'required|max:100',
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_fails_when_not_numeric(): void
    {
        $validator = Validator::make([
            'age' => 'dua puluh',
        ], [
            'age' => 'numeric',
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_passes_when_numeric(): void
    {
        $validator = Validator::make([
            'age' => '25',
        ], [
            'age' => 'numeric',
        ]);

        $this->assertFalse($validator->fails());
    }
}