<?php

namespace Laxity7\LaravelSwagger\Tests\Parsers;

use Illuminate\Routing\Route as LaravelRoute;
use Laxity7\LaravelSwagger\Parsers\Route;
use Laxity7\LaravelSwagger\Parsers\RouteReflection;
use Laxity7\LaravelSwagger\Tests\Stubs\Controllers\UserController;
use Laxity7\LaravelSwagger\Tests\Stubs\Requests\UserShowRequest;
use PHPUnit\Framework\TestCase;

final class RouteReflectionTest extends TestCase
{
    /**
     * @dataProvider getMethodRequestClassData
     */
    public function testGetMethodRequestClass(array $method, ?string $expected): void
    {
        $rr = new RouteReflection($this->getRoute($method));
        $class = $rr->getMethodRequestClass();

        $this->assertSame($expected, $class);
    }

    public static function getMethodRequestClassData(): array
    {
        return [
            'request from params' => [[UserController::class, 'show'], UserShowRequest::class],
            'request from phpDoc' => [[UserController::class, 'showFromDoc'], UserShowRequest::class],
            'request from attributes' => [[UserController::class, 'showFromAttribute'], UserShowRequest::class],
            'no request' => [[UserController::class, 'details'], null],
        ];
    }

    private function getRoute(array $action): Route
    {
        return new Route(new LaravelRoute('GET', '/', $action));
    }
}
