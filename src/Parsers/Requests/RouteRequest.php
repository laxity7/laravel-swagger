<?php

namespace Mtrajano\LaravelSwagger\Parsers\Requests;

use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Support\Str;
use Mtrajano\LaravelSwagger\Parsers\ReflectionHelper;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use ReflectionClass;

final class RouteRequest
{
    private ReflectionClass $reflection;

    public function __construct(
        private readonly string $requestClass,
    ) {
        $this->reflection = ReflectionHelper::getClass($this->requestClass, $this);
    }

    public function getRequest(): LaravelRequest
    {
        return app($this->requestClass);
    }

    public function getRules(): array
    {
        $request = $this->requestClass;
        if (!is_subclass_of($request, LaravelRequest::class) || !method_exists($request, 'rules')) {
            return [];
        }

        return $this->getRequest()->rules();
    }

    public function getClassDocBlock(): DocBlock
    {
        return ReflectionHelper::parseDocBlock($this->reflection);
    }

    public function getDescription(): string
    {
        return $this->getClassDocBlock()?->getDescription()->render() ?? '';
    }

    public function getSummary(): string
    {
        return $this->getClassDocBlock()?->getSummary() ?? '';
    }

    public function getFieldDescription(string $field): string
    {
        $default = Str::headline($field);
        $properties = $this->getClassDocBlock()?->getTagsByName('property') ?? [];
        /** @var Property $property */
        foreach ($properties as $property) {
            if ($property->getVariableName() === $field) {
                return $property->getDescription()?->render() ?? '';
            }
        }

        if (!$this->reflection->hasProperty($field)) {
            return $default;
        }

        $property = $this->reflection->getProperty($field);
        $docBlock = ReflectionHelper::parseDocBlock($property);
        if ($docBlock->getSummary()) {
            return $docBlock->getSummary();
        }

        return $docBlock->getTagsByName('var')[0]?->getDescription()->render() ?? $default;
    }
}
