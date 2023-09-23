<?php

namespace Laxity7\LaravelSwagger\Formatters;

use Laxity7\LaravelSwagger\LaravelSwaggerException;

final class JsonFormatter extends Formatter
{
    public function format(): string
    {
        if (!extension_loaded('json')) {
            throw new LaravelSwaggerException('JSON extension must be loaded to use the json output format');
        }

        return json_encode($this->docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
