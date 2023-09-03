<?php

namespace Mtrajano\LaravelSwagger\Tests\Parsers\Requests\Parameters;

use Illuminate\Routing\Route as LaravelRoute;
use Mtrajano\LaravelSwagger\DataObjects\Route;
use Mtrajano\LaravelSwagger\Parsers\Requests\Generators\PathParameterGenerator;
use Mtrajano\LaravelSwagger\Tests\Stubs\Enums\IntEnum;
use Mtrajano\LaravelSwagger\Tests\Stubs\Enums\NamedEnum;
use Mtrajano\LaravelSwagger\Tests\Stubs\Enums\StringEnum;
use Mtrajano\LaravelSwagger\Tests\TestCase;

final class PathParameterGeneratorTest extends TestCase
{
    public function testRequiredParameter(): void
    {
        $pathParameters = $this->getPathParameters('/users/{id}', static fn(int $id) => '');

        self::assertSame('path', $pathParameters[0]['in']);
        self::assertSame('id', $pathParameters[0]['name']);
        self::assertSame('integer', $pathParameters[0]['schema']['type']);
        self::assertTrue($pathParameters[0]['required']);
    }

    public function testOptionalParameter(): void
    {
        $pathParameters = $this->getPathParameters('/users/{id?}');

        self::assertFalse($pathParameters[0]['required']);
    }

    public function testMultipleParameters(): void
    {
        $pathParameters = $this->getPathParameters(
            '/users/{username}/{id?}',
            /** @param  string  $username  User name foo */
            static fn(string $username, int $id) => ''
        );

        self::assertSame('username', $pathParameters[0]['name']);
        self::assertSame('User name foo', $pathParameters[0]['description']);
        self::assertTrue($pathParameters[0]['required']);
        self::assertSame('string', $pathParameters[0]['schema']['type']);

        self::assertSame('id', $pathParameters[1]['name']);
        self::assertSame('Id', $pathParameters[1]['description']);
        self::assertFalse($pathParameters[1]['required']);
        self::assertSame('integer', $pathParameters[1]['schema']['type']);
    }

    public function testEmptyParameters(): void
    {
        $pathParameters = $this->getPathParameters('/users');

        self::assertEmpty($pathParameters);
    }

    public function testPatternParameters(): void
    {
        $pathParameters = $this->getParameters(
            $this->getRoute('/users/{status}/{uuid}')
                ->whereUuid('uuid')
                ->whereIn('status', ['active', 'deleted'])
        );

        self::assertCount(2, $pathParameters);

        $enum = [
            'type' => 'string',
            'enum' => ['active', 'deleted'],
        ];
        self::assertSame('status', $pathParameters[0]['name']);
        self::assertSame($enum, $pathParameters[0]['schema']);

        $uuid = [
            'type' => 'string',
            'format' => 'uuid',
        ];
        self::assertSame('uuid', $pathParameters[1]['name']);
        self::assertSame($uuid, $pathParameters[1]['schema']);
    }

    public function testEnumParameters(): void
    {
        $pathParameters = $this->getParameters(
            $this->getRoute(
                '/users/{status}/{status_int}/{status_string}/{status_mixed}',
                static fn(
                    NamedEnum $status,
                    IntEnum $status_int,
                    StringEnum $status_string,
                    StringEnum $status_mixed = StringEnum::ACTIVE
                ) => ''
            )
                ->whereIn('status_mixed', ['active', 'deleted'])
        );

        self::assertCount(4, $pathParameters);

        $enum = [
            'type' => 'string',
            'enum' => ['ACTIVE', 'INACTIVE'],
        ];
        self::assertSame('status', $pathParameters[0]['name']);
        self::assertSame($enum, $pathParameters[0]['schema']);

        $enum = [
            'type' => 'integer',
            'enum' => [1, 0],
        ];
        self::assertSame('status_int', $pathParameters[1]['name']);
        self::assertSame($enum, $pathParameters[1]['schema']);

        $enum = [
            'type' => 'string',
            'enum' => ['active', 'inactive'],
        ];
        self::assertSame('status_string', $pathParameters[2]['name']);
        self::assertSame($enum, $pathParameters[2]['schema']);

        $enum = [
            'type' => 'string',
            'default' => 'active',
            'enum' => ['active', 'deleted'],
        ];
        self::assertSame('status_mixed', $pathParameters[3]['name']);
        self::assertSame($enum, $pathParameters[3]['schema']);
    }

    private function getPathParameters(string $uri, callable|array $action = null): array
    {
        return $this->getParameters($this->getRoute($uri, $action));
    }

    private function getParameters(LaravelRoute $route): array
    {
        return (new PathParameterGenerator())->getParameters(new Route($route));
    }

    private function getRoute(string $uri, callable|array $action = null): LaravelRoute
    {
        return new LaravelRoute('GET', $uri, $action ?? static fn() => '');
    }
}
