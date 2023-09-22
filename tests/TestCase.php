<?php

namespace Mtrajano\LaravelSwagger\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Laravel\Passport\Http\Middleware\CheckForAnyScope;
use Laravel\Passport\Http\Middleware\CheckScopes;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Mtrajano\LaravelSwagger\SwaggerServiceProvider;
use Mtrajano\LaravelSwagger\Tests\Stubs\Controllers\ApiController;
use Mtrajano\LaravelSwagger\Tests\Stubs\Controllers\UserController;
use Mtrajano\LaravelSwagger\Tests\Stubs\Middleware\RandomMiddleware;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->disableValidation();
    }

    private function disableValidation(): void
    {
        (function () {
            /** @var Application $this */
            unset($this->afterResolvingCallbacks['Illuminate\Contracts\Validation\ValidatesWhenResolved']);
        })->call(app());
    }

    final protected function getPackageProviders($app): array
    {
        return [
            SwaggerServiceProvider::class,
            PassportServiceProvider::class
        ];
    }

    final protected function getEnvironmentSetUp($app): void
    {
        /** @var Router $router */
        $router = $app['router'];
        $router->middleware(['some-middleware', 'scope:user-read'])->group(static function () use ($router) {
            $router->get('/users', [UserController::class, 'index']);
            $router->get('/users/{id}', [UserController::class, 'show'])->whereNumber('id');
            $router->post('/users', [UserController::class, 'store'])->middleware('scopes:user-write,user-read');
            $router->get('/users/{id}/details', [UserController::class, 'details']);
            $router->get('/users/{id}/details/{detail_id?}', [UserController::class, 'details'])->whereNumber('id')->whereUuid('detail_id');
            $router->get('/users/{foo}', [UserController::class, 'details'])->whereIn('foo', ['foo', 'bar']);
            $router->get('/users/ping', static fn() => 'pong');
        });

        $router->get('/api', [ApiController::class, 'index'])->middleware(RandomMiddleware::class);
        $router->put('/api/store', [ApiController::class, 'store']);

        $router->aliasMiddleware('scopes', CheckScopes::class);
        $router->aliasMiddleware('scope', CheckForAnyScope::class);

        Passport::tokensCan([
            'user-read' => 'Read user information such as email, name and phone number',
            'user-write' => 'Update user information',
        ]);
    }

    final public static function assertContainsAssocArray(
        array $needle,
        array $haystack,
        string $message = 'Failed asserting that array contains the specified array.'
    ): void {
        foreach ($needle as $key => $value) {
            static::assertArrayHasKey($key, $haystack, $message);
            static::assertEquals($value, $haystack[$key], $message);
        }
    }
}
