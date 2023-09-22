<?php

namespace Laxity7\LaravelSwagger\Parsers;

use Exception;
use Laravel\Passport\Http\Middleware\CheckForAnyScope;
use Laravel\Passport\Http\Middleware\CheckScopes;
use Laxity7\LaravelSwagger\DataObjects;
use Laxity7\LaravelSwagger\DataObjects\Path;
use Laxity7\LaravelSwagger\Generator;
use Laxity7\LaravelSwagger\Parsers\Requests\RequestParser;

final class MethodParser
{
    public function __construct(
        private readonly Route $route,
        private readonly RequestParser $requestParser,
        private readonly ResponseParser $responseParser,
        private readonly bool $hasSecurityDefinitions,
    ) {
    }

    public function isSkipped(): bool
    {
        return $this->route->getMethodDocBlock()?->hasTag(Generator::TAG_IGNORE) ?? false;
    }

    public function parse(): Path
    {
        [$isDeprecated, $summary, $description] = $this->parseActionDocBlock();

        return new Path(
            summary: $summary,
            description: $description,
            deprecated: $isDeprecated,
            parameters: $this->requestParser->getParameters(),
            responses: $this->responseParser->getResponses(),
            security: $this->getSecurity(),
        );
    }

    private function getSecurity(): array
    {
        if (!$this->hasSecurityDefinitions) {
            return [];
        }

        $security = [];
        foreach ($this->route->middleware as $middleware) {
            if (!$this->isPassportScopeMiddleware($middleware)) {
                continue;
            }
            $security[] = $middleware->parameters;
        }

        if (!empty($security)) {
            $security = [Generator::SECURITY_DEFINITION_NAME => array_unique(array_merge(...$security))];
        }

        return $security;
    }

    private function parseActionDocBlock(): array
    {
        $docBlock = $this->route->getMethodDocBlock();
        if ($docBlock === null) {
            return [false, '', ''];
        }

        try {
            $isDeprecated = $docBlock->hasTag('deprecated');
            $summary = $docBlock->getSummary();
            $description = $docBlock->getDescription()->render();

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
}
