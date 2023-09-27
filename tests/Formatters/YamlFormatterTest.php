<?php

namespace Laxity7\LaravelSwagger\Tests\Formatters;

use Laxity7\LaravelSwagger\Formatters\YamlFormatter;
use PHPUnit\Framework\TestCase;

final class YamlFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $this->assertEquals(
            <<<YAML
name:
    firstName: John
    lastName: ''
age: 30
city: 'New York'

YAML,
            (new YamlFormatter())->format([
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
