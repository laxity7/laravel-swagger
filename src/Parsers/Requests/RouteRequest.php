<?php

namespace Laxity7\LaravelSwagger\Parsers\Requests;

use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Support\Str;
use Laxity7\LaravelSwagger\Parsers\DocBlock;
use Laxity7\LaravelSwagger\Parsers\ReflectionHelper;
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
        return $this->getClassDocBlock()?->getSummary() ?: $this->getClassDocBlock()?->getDescription()->render() ?? '';
    }

    public function getFieldDescription(string $field): string
    {
        $default = Str::headline($field);

        return ReflectionHelper::getPropertyDescription(
            $this->reflection,
            $field,
            $this->getClassDocBlock()->getTagDescriptionForVariable('property', $field, $default)
        );
    }
}
