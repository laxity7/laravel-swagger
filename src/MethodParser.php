<?php

namespace Mtrajano\LaravelSwagger;

use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Laravel\Passport\Http\Middleware\CheckForAnyScope;
use Laravel\Passport\Http\Middleware\CheckScopes;
use Mtrajano\LaravelSwagger\DataObjects\Path;
use Mtrajano\LaravelSwagger\DataObjects\Route;
use Mtrajano\LaravelSwagger\Parameters\BodyParameterGenerator;
use Mtrajano\LaravelSwagger\Parameters\PathParameterGenerator;
use Mtrajano\LaravelSwagger\Parameters\QueryParameterGenerator;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;

final class MethodParser
{
    private ReflectionMethod $reflectionMethod;

    public function __construct(
        private readonly Route $route,
        private readonly string $methodName,
        private readonly bool $hasSecurityDefinitions,
        private readonly bool $parseDocBlock,
        private readonly DocBlockFactory $docParser,
    ) {
    }

    public function parse(): Path
    {
        [$isDeprecated, $summary, $description] = $this->parseActionDocBlock();

        return new Path(
            summary: $summary,
            description: $description,
            deprecated: $isDeprecated,
            parameters: $this->getActionParameters(),
            responses: $this->getResponses(),
            security: $this->getSecurity(),
        );
    }

    private function getActionParameters(): array
    {
        $rules = $this->getFormRules() ?: [];

        $parameters = (new PathParameterGenerator($this->route->originalUri()))->getParameters();

        if (!empty($rules)) {
            $parameterGenerator = match ($this->methodName) {
                'post', 'put', 'patch' => new BodyParameterGenerator($rules),
                default => new QueryParameterGenerator($rules),
            };

            $parameters = array_merge($parameters, $parameterGenerator->getParameters());
        }

        return $parameters;
    }

    private function getSecurity(): array
    {
        if (!$this->hasSecurityDefinitions) {
            return [];
        }

        $security = [];
        foreach ($this->route->middleware() as $middleware) {
            if (!$this->isPassportScopeMiddleware($middleware)) {
                continue;
            }

            $security = [
                ...$security,
                ...$middleware->parameters
            ];
        }

        if (!empty($security)) {
            $security = [Generator::SECURITY_DEFINITION_NAME => array_unique($security)];
        }

        return $security;
    }

    private function getFormRules(): array
    {
        $reflectionMethod = $this->getActionClassInstance();
        if (!$reflectionMethod) {
            return [];
        }

        $parameters = $reflectionMethod->getParameters();
        foreach ($parameters as $parameter) {
            $className = $parameter->getType()?->getName();
            if (!$className) {
                continue;
            }

            if (is_subclass_of($className, FormRequest::class)) {
                return app($className)->rules();
            }
        }

        return [];
    }

    private function getActionClassInstance(): ?ReflectionMethod
    {
        if (!isset($this->reflectionMethod)) {
            [$class, $method] = Str::parseCallback($this->route->action());

            if (!$class || !$method) {
                return null;
            }

            $this->reflectionMethod = new ReflectionMethod($class, $method);
        }

        return $this->reflectionMethod;
    }

    private function parseActionDocBlock(): array
    {
        $actionInstance = $this->getActionClassInstance();
        $docBlock = $actionInstance?->getDocComment() ?: '';

        if (empty($docBlock) || !$this->parseDocBlock) {
            return [false, '', ''];
        }

        try {
            $parsedComment = $this->docParser->create($docBlock);

            $isDeprecated = $parsedComment->hasTag('deprecated');
            $summary = $parsedComment->getSummary();
            $description = $parsedComment->getDescription()->render();

            return [$isDeprecated, $summary, $description];
        } catch (Exception) {
            return [false, '', ''];
        }
    }

    private function isPassportScopeMiddleware(DataObjects\Middleware $middleware): bool
    {
        $resolver = app('router')->getMiddleware()[$middleware->name] ?? '';

        return in_array($resolver, [CheckScopes::class, CheckForAnyScope::class], true);
    }


    private function getResponses(): array
    {
        return [
            '200' => [
                'description' => 'OK',
            ],
        ];
    }
}
