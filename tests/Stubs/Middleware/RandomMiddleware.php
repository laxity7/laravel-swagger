<?php

namespace Laxity7\LaravelSwagger\Tests\Stubs\Middleware;

use Illuminate\Http\Request;

final class RandomMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        return $next($request);
    }
}
