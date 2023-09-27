<?php

namespace Laxity7\LaravelSwagger\Tests\Formatters;

use Laxity7\LaravelSwagger\Formatters\JsonFormatter;
use PHPUnit\Framework\TestCase;

final class JsonFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $this->assertEquals(
            <<<JSON
{
    "name": {
        "firstName": "John",
        "lastName": ""
    },
    "age": 30,
    "city": "New York"
}
JSON,
            (new JsonFormatter())->format([
                'name' => [
                    'firstName' => 'John',
                    'lastName' => '',
                ],
                'age' => 30,
                'city' => 'New York',
            ])
        );
    }
}
