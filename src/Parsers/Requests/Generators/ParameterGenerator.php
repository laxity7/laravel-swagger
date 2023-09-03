<?php

namespace Mtrajano\LaravelSwagger\Parsers\Requests\Generators;

use Mtrajano\LaravelSwagger\DataObjects\Route;
use Mtrajano\LaravelSwagger\Enums\Method;

interface ParameterGenerator
{
    public function isNeedParsing(Route $route, Method $method): bool;

    public function getParameters(Route $route): array;
}
