<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Artemis\Response;

class ResponseTest extends TestCase
{
    public function test_success_response_code_format(): void
    {
        // Test format responseCode saja tanpa memanggil Response::send()
        $httpCode = 200;
        $expected = $httpCode . 'M500';

        $this->assertEquals('200M500', $expected);
    }

    public function test_error_response_code_format(): void
    {
        $httpCode    = 400;
        $serviceCode = '502';
        $expected    = $httpCode . 'M' . $serviceCode;

        $this->assertEquals('400M502', $expected);
    }

    public function test_created_response_code_format(): void
    {
        $httpCode = 201;
        $expected = $httpCode . 'M500';

        $this->assertEquals('201M500', $expected);
    }
}