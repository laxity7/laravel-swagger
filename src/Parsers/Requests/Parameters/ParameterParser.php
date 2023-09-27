<?php

namespace Laxity7\LaravelSwagger\Parsers\Requests\Parameters;

use Laxity7\LaravelSwagger\Enums\Method;
use Laxity7\LaravelSwagger\Parsers\Route;

interface ParameterParser
{
    public function isNeedParsing(Route $route, Method $method): bool;

    public function getParameters(Route $route): array;
}
