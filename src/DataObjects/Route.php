<?php

namespace Mtrajano\LaravelSwagger\DataObjects;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Arr;
use Mtrajano\LaravelSwagger\Enums\Method;
use Mtrajano\LaravelSwagger\Parsers\ReflectionHelper;
use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

final class Route
{
    /** @var Middleware[] */
    private array $middleware;

    public function __construct(
        public readonly LaravelRoute $route,
        public readonly bool $parseDocBlock = true,
    ) {
        $this->middleware = $this->formatMiddleware();
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
     * @return Middleware[]
     */
    public function middleware(): array
    {
        return $this->middleware;
    }

    public function getReflectionObject(): ?ReflectionClass
    {
        return ReflectionHelper::getRouteClass($this->route->getAction());
    }

    public function getReflectionMethod(): ReflectionFunction|ReflectionMethod|null
    {
        return ReflectionHelper::getRouteAction($this->route->getAction());
    }

    public function getMethodDocBlock(): ?DocBlock
    {
        if (!$this->parseDocBlock) {
            return null;
        }

        $docBlock = $this->getReflectionMethod()?->getDocComment() ?: '';
        if (empty($docBlock)) {
            return null;
        }

        return ReflectionHelper::parseDocBlock($docBlock);
    }

    /**
     * @return ReflectionParameter[]
     */
    public function getMethodParameters(): array
    {
        return $this->getReflectionMethod()?->getParameters() ?? [];
    }

    /**
     * @return string[]
     */
    public function pathParameters(): array
    {
        return $this->route->parameterNames();
    }

    public function action(): string
    {
        return $this->route->getActionName();
    }

    /**
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
}
