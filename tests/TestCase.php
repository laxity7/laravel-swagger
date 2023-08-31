<?php

namespace Mtrajano\LaravelSwagger\Tests;

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
            $router->get('/users/{id}', [UserController::class, 'show']);
            $router->post('/users', [UserController::class, 'store'])->middleware('scopes:user-write,user-read');
            $router->get('/users/details', [UserController::class, 'details']);
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
}
