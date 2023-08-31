<?php

namespace Mtrajano\LaravelSwagger\DataObjects;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use function Mtrajano\LaravelSwagger\strip_optional_char;

final class Route
{
    /** @var Middleware[] */
    private array $middleware;

    public function __construct(
        private LaravelRoute $route
    ) {
        $this->route = $route;
        $this->middleware = $this->formatMiddleware();
    }

    public function originalUri(): string
    {
        $uri = $this->route->uri();

        if (!Str::startsWith($uri, '/')) {
            $uri = '/'.$uri;
        }

        return $uri;
    }

    public function uri(): string
    {
        return strip_optional_char($this->originalUri());
    }

    /**
     * @return Middleware[]
     */
    public function middleware(): array
    {
        return $this->middleware;
    }

    public function action(): string
    {
        return $this->route->getActionName();
    }

    /**
     * @return string[]
     */
    public function methods(): array
    {
        return array_map('strtolower', $this->route->methods());
    }

    /**
     * @return Middleware[]
     */
    private function formatMiddleware(): array
    {
        $middleware = $this->route->getAction('middleware');

        return array_map(static fn($middleware) => new Middleware($middleware), Arr::wrap($middleware));
    }
}
