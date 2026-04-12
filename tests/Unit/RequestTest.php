<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Artemis\Request;

class RequestTest extends TestCase
{
    public function test_input_returns_default_when_key_not_found(): void
    {
        $request = new Request();
        $value   = $request->input('tidak_ada', 'default');

        $this->assertEquals('default', $value);
    }

    public function test_body_returns_empty_array_when_no_input(): void
    {
        $request = new Request();
        $body    = $request->body();

        $this->assertIsArray($body);
    }

    public function test_method_returns_request_method(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $request = new Request();
        $this->assertEquals('GET', $request->method());
    }

    public function test_header_returns_null_when_not_found(): void
    {
        $request = new Request();
        $value   = $request->header('X-NOT-EXIST');

        $this->assertNull($value);
    }

    public function test_header_returns_value_when_exists(): void
    {
        $_SERVER['HTTP_X_PARTNER_ID'] = 'artemis-123';

        $request = new Request();
        $value   = $request->header('X-PARTNER-ID');

        $this->assertEquals('artemis-123', $value);
    }
}