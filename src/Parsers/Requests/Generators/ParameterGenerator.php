<?php

namespace Mtrajano\LaravelSwagger\Parsers\Requests\Generators;

use Mtrajano\LaravelSwagger\Enums\Method;
use Mtrajano\LaravelSwagger\Parsers\Route;

interface ParameterGenerator
{
    public function isNeedParsing(Route $route, Method $method): bool;

    public function getParameters(Route $route): array;
}
