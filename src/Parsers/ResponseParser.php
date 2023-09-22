<?php

namespace Laxity7\LaravelSwagger\Parsers;

use Laxity7\LaravelSwagger\Enums\Method;

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
