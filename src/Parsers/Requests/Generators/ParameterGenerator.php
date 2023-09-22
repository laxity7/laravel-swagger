<?php

namespace Laxity7\LaravelSwagger\Parsers\Requests\Generators;

use Laxity7\LaravelSwagger\Enums\Method;
use Laxity7\LaravelSwagger\Parsers\Route;

interface ParameterGenerator
{
    public function isNeedParsing(Route $route, Method $method): bool;

    public function getParameters(Route $route): array;
}
