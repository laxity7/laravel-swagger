<?php

namespace Mtrajano\LaravelSwagger\Parsers\Requests;

class Parameter implements \ArrayAccess
{
    public function __construct(
        public readonly string $in,
        public readonly string $name,
        public readonly array $schema,
        public readonly bool $required,
        public readonly string $description,
    ) {
    }

    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    public function offsetSet($offset, $value)
    {
        throw new \Exception('Parameter is immutable');
    }

    public function offsetUnset($offset)
    {
        throw new \Exception('Parameter is immutable');
    }
}
