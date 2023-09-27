<?php

namespace Laxity7\LaravelSwagger;

use Laxity7\LaravelSwagger\Formatters\Formatter;

final class FormatterManager
{
    public function __construct(
        private array $docs,
        /**
         * @var list<string, Formatter>
         */
        private array $formatters
    ) {

    }

    /**
     * @throws LaravelSwaggerException
     */
    private function getFormatter(string $format): Formatter
    {
        $formatter = $this->formatters[$format] ?? throw new LaravelSwaggerException('Invalid format passed');

        return new $formatter();
    }

    public function format(string $format): string
    {
        return $this->getFormatter($format)->format($this->docs);
    }
}
