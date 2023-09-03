<?php

namespace Mtrajano\LaravelSwagger\Parsers\Requests;

use Mtrajano\LaravelSwagger\DataObjects\Route;
use Mtrajano\LaravelSwagger\Enums\Method;
use Mtrajano\LaravelSwagger\Parsers\Requests\Generators\ParameterGenerator;

final class RequestParser
{
    public function __construct(
        private readonly Route $route,
        private readonly Method $methodName,
        /** @var ParameterGenerator[] */
        private readonly array $generators,
    ) {
    }

    public function getParameters(): array
    {
        $parameters = [];
        foreach ($this->generators as $generator) {
            /** @var ParameterGenerator $generator */
            $generator = app($generator);
            if ($generator->isNeedParsing($this->route, $this->methodName)) {
                $parameters = [
                    ...$parameters,
                    ...$generator->getParameters($this->route)
                ];
            }
        }

        return $parameters;
    }
}
