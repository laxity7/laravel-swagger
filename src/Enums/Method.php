<?php

namespace Mtrajano\LaravelSwagger\Enums;

use InvalidArgumentException;

enum Method
{
    case GET;
    case POST;
    case PUT;
    case PATCH;
    case DELETE;
    case OPTIONS;
    case HEAD;

    /**
     * @param  string[]  $methods
     * @return self[]
     */
    public static function fromArray(array $methods): array
    {
        return array_map(fn(string|self $method) => self::from($method), $methods);
    }

    public function is(Method $method): bool
    {
        return $this->name === $method->name;
    }

    public static function from(string|self $value): static
    {
        if ($value instanceof self) {
            return $value;
        }

        return match (strtoupper($value)) {
            'GET' => self::GET,
            'POST' => self::POST,
            'PUT' => self::PUT,
            'PATCH' => self::PATCH,
            'DELETE' => self::DELETE,
            'OPTIONS' => self::OPTIONS,
            'HEAD' => self::HEAD,
            default => throw new InvalidArgumentException("Invalid method: {$value}"),
        };
    }

    public function isOneOf(Method ...$methods): bool
    {
        foreach ($methods as $method) {
            if ($this->is($method)) {
                return true;
            }
        }

        return false;
    }

    public function lowerValue(): string
    {
        return strtolower($this->name);
    }
}
