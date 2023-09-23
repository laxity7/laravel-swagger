<?php

namespace Laxity7\LaravelSwagger\Parsers;

use Illuminate\Http\Request as LaravelRequest;
use Laxity7\LaravelSwagger\Attributes\Request;
use Laxity7\LaravelSwagger\Generator;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

final class RouteReflection
{
    private array $action;

    public function __construct(
        private Route $route
    ) {
        $this->action = $this->route->route->getAction();
    }

    public function getClass(): ?ReflectionClass
    {
        return ReflectionHelper::getRouteClass($this->action);
    }

    public function getClassDocBlock(): DocBlock
    {
        return ReflectionHelper::parseDocBlock($this->getClass());
    }

    public function getMethod(): ReflectionFunction|ReflectionMethod|null
    {
        return ReflectionHelper::getRouteAction($this->action);
    }

    public function getMethodDocBlock(): DocBlock
    {
        return ReflectionHelper::parseDocBlock($this->getMethod());
    }

    /**
     * @return ReflectionParameter[]
     */
    public function getMethodParameters(): array
    {
        return $this->getMethod()?->getParameters() ?? [];
    }

    public function getMethodRequestClass(): ?string
    {
        $reflectionMethod = $this->getMethod();
        if (!$reflectionMethod) {
            return null;
        }

        $strategies = [
            'byAttribute' => static function () use ($reflectionMethod) {
                $attribute = $reflectionMethod->getAttributes(Request::class)[0] ?? null;
                return $attribute?->newInstance()->request;
            },
            'byParameter' => static function () use ($reflectionMethod) {
                foreach ($reflectionMethod->getParameters() as $parameter) {
                    $className = $parameter->getType()?->getName();
                    if (!$className) {
                        continue;
                    }

                    if (is_subclass_of($className, LaravelRequest::class)) {
                        return $className;
                    }
                }

                return null;
            },
            'byTag' => function () {
                $tag = $this->getMethodDocBlock()?->getTagsByName(Generator::TAG_REQUEST)[0] ?? null;
                if ($tag === null) {
                    return null;
                }
                $className = $tag->getDescription()->render();

                return ReflectionHelper::normalizeClassType($this->getClass(), $className);
            },
        ];

        foreach ($strategies as $strategy) {
            $className = $strategy($reflectionMethod);
            if ($className) {
                return $className;
            }
        }

        return null;
    }


}
