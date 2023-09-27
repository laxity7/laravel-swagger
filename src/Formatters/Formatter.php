<?php

namespace Laxity7\LaravelSwagger\Formatters;

interface Formatter
{
    public function format(array $docs): string;
}
