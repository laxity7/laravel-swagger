<?php

namespace Laxity7\LaravelSwagger\Parsers;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Arr;
use Laxity7\LaravelSwagger\DataObjects\Middleware;
use Laxity7\LaravelSwagger\Enums\Method;
use Laxity7\LaravelSwagger\Parsers\Requests\RouteRequest;
use phpDocumentor\Reflection\DocBlock;
use ReflectionParameter;

final class Route
{
    /** @var Middleware[] */
    public readonly array $middleware;
    public readonly RouteReflection $reflection;
    public readonly ?RouteRequest $request;

    public function __construct(
        public readonly LaravelRoute $route,
    ) {
        $this->middleware = $this->formatMiddleware();
        $this->reflection = new RouteReflection($this);

        $className = $this->reflection->getMethodRequestClass();
        $this->request = $className ? new RouteRequest($className) : null;
    }

    public function originalUri(): string
    {
        return '/'.ltrim($this->route->uri(), '/');
    }

    public function uri(): string
    {
        return str_replace('?', '', $this->originalUri());
    }

    /**
     * @return ReflectionParameter[]
     */
    public function getParameters(): array
    {
        return $this->reflection->getMethod()?->getParameters() ?? [];
    }

    /**
     * Get all the parameter names for the route.
     *
     * @return string[]
     */
    public function pathParameterNames(): array
    {
        return $this->route->parameterNames();
    }

    /**
     * Get the action name for the route.
     *
     * @return string
     */
    public function action(): string
    {
        return $this->route->getActionName();
    }

    /**
     * Get the HTTP verbs the route responds to.
     *
     * @return Method[]
     */
    public function methods(): array
    {
        return Method::fromArray($this->route->methods());
    }

    /**
     * @return Middleware[]
     */
    private function formatMiddleware(): array
    {
        $middleware = $this->route->getAction('middleware');

        return array_map(static fn($middleware) => new Middleware($middleware), Arr::wrap($middleware));
    }

    public function getClassDocBlock(): ?DocBlock
    {
        return $this->reflection->getClassDocBlock();
    }

    public function getMethodDocBlock(): ?DocBlock
    {
        return $this->reflection->getMethodDocBlock();
    }
}
