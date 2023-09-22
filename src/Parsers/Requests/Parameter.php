<?php

namespace Mtrajano\LaravelSwagger\Parsers\Requests;

class Parameter
{
    public function __construct(
        public readonly string $in,
        public readonly string $name,
        public readonly array $schema,
        public readonly bool $required,
        public readonly string $description,
    ) {
    }
}
