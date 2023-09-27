<?php

namespace Laxity7\LaravelSwagger\Formatters;

use Laxity7\LaravelSwagger\LaravelSwaggerException;

final class YamlFormatter implements Formatter
{
    public function format(array $docs): string
    {
        if (extension_loaded('yaml')) {
            return yaml_emit($docs);
        }

        if (class_exists(\Symfony\Component\Yaml\Yaml::class)) {
            return \Symfony\Component\Yaml\Yaml::dump($docs);
        }

        throw new LaravelSwaggerException('YAML extension must be loaded to use the yaml output format');
    }
}
