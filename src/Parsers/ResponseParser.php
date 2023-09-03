<?php

namespace Mtrajano\LaravelSwagger\Parsers;

use Mtrajano\LaravelSwagger\DataObjects\Route;
use Mtrajano\LaravelSwagger\Enums\Method;

final class ResponseParser
{
    public function __construct(
        private readonly Route $route,
        private readonly Method $method,
    ) {
    }

    public function getResponses(): array
    {
        return [
            '200' => [
                'description' => 'OK',
            ],
        ];
    }
}
