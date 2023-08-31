<?php

namespace Mtrajano\LaravelSwagger\Tests\Parameters;

use Mtrajano\LaravelSwagger\Parameters\PathParameterGenerator;
use Mtrajano\LaravelSwagger\Tests\TestCase;

final class PathParameterGeneratorTest extends TestCase
{
    public function testRequiredParameter(): void
    {
        $pathParameters = $this->getPathParameters('/users/{id}');

        $this->assertSame('path', $pathParameters[0]['in']);
        $this->assertSame('id', $pathParameters[0]['name']);
        $this->assertTrue($pathParameters[0]['required']);
    }

    public function testOptionalParameter(): void
    {
        $pathParameters = $this->getPathParameters('/users/{id?}');

        $this->assertFalse($pathParameters[0]['required']);
    }

    public function testMultipleParameters(): void
    {
        $pathParameters = $this->getPathParameters('/users/{username}/{id?}');

        $this->assertSame('username', $pathParameters[0]['name']);
        $this->assertTrue($pathParameters[0]['required']);

        $this->assertSame('id', $pathParameters[1]['name']);
        $this->assertFalse($pathParameters[1]['required']);
    }

    public function testEmptyParameters(): void
    {
        $pathParameters = $this->getPathParameters('/users');

        $this->assertEmpty($pathParameters);
    }

    private function getPathParameters(string $uri): array
    {
        return (new PathParameterGenerator($uri))->getParameters();
    }
}
