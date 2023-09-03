<?php

namespace Mtrajano\LaravelSwagger\DataObjects;

final class Path
{
    public function __construct(
        public readonly string $summary,
        public readonly string $description,
        public readonly bool $deprecated,
        public readonly array $parameters,
        public readonly array $responses,
        public readonly array $security,
    ) {
    }

    public function toArray(): array
    {
        $data = (array) $this;
        if (!$this->deprecated) {
            unset($data['deprecated']);
        }

        foreach (['parameters', 'security'] as $item) {
            if (empty($data[$item])) {
                unset($data[$item]);
            }
        }

        return $data;
    }
}
