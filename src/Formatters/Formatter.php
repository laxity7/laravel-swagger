<?php

namespace Laxity7\LaravelSwagger\Formatters;

abstract class Formatter
{
    public function __construct(protected array $docs)
    {
    }

    abstract public function format(): string;
}
