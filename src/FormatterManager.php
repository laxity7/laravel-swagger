<?php

namespace Laxity7\LaravelSwagger;

use Laxity7\LaravelSwagger\Formatters\Formatter;

final class FormatterManager
{
    private Formatter $formatter;

    public function __construct(private array $docs)
    {
        $this->formatter = $this->getFormatter('json');
    }

    public function setFormat(string $format): self
    {
        $this->formatter = $this->getFormatter($format);

        return $this;
    }

    /**
     * @throws LaravelSwaggerException
     */
    private function getFormatter(string $format): Formatter
    {
        return match (strtolower($format)) {
            'json' => new Formatters\JsonFormatter($this->docs),
            'yaml' => new Formatters\YamlFormatter($this->docs),
            default => throw new LaravelSwaggerException('Invalid format passed'),
        };
    }

    public function format(): string
    {
        return $this->formatter->format();
    }
}
