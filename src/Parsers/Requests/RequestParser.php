<?php

namespace Laxity7\LaravelSwagger\Parsers\Requests;

use Exception;
use Laxity7\LaravelSwagger\Enums\Method;
use Laxity7\LaravelSwagger\Parsers\Requests\Generators\ParameterGenerator;
use Laxity7\LaravelSwagger\Parsers\Route;

final class RequestParser
{
    public function __construct(
        private readonly Route $route,
        private readonly Method $methodName,
        /** @var list<class-string<ParameterGenerator>> */
        private readonly array $generators,
    ) {
    }

    public function getParameters(): array
    {
        $parameters = [];
        foreach ($this->generators as $generator) {
            if (!is_subclass_of($generator, ParameterGenerator::class)) {
                throw new Exception(sprintf('Generator %s must implement %s', $generator, ParameterGenerator::class));
            }
            /** @var ParameterGenerator $generator */
            $generator = app($generator);
            if (!$generator->isNeedParsing($this->route, $this->methodName)) {
                continue;
            }

            $parameters[] = $generator->getParameters($this->route);
        }

        return array_merge(...$parameters);
    }
}
