<?php

namespace Laxity7\LaravelSwagger\DataObjects;

final class Middleware
{
    public readonly string $name;
    public readonly array $parameters;

    public function __construct(string $middleware)
    {
        $tokens = explode(':', $middleware, 2);
        $this->name = $tokens[0];
        $this->parameters = isset($tokens[1]) ? explode(',', $tokens[1]) : [];
    }
}
