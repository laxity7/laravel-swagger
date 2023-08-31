<?php

namespace Mtrajano\LaravelSwagger\Parameters\Concerns;

use Illuminate\Support\Str;

trait GeneratesFromRules
{
    private function splitRules(array|string $rules): array
    {
        if (is_string($rules)) {
            return explode('|', $rules);
        }

        return $rules;
    }

    private function getParamType(array $paramRules): string
    {
        return match (true) {
            in_array('integer', $paramRules, true) => 'integer',
            in_array('numeric', $paramRules, true) => 'number',
            in_array('boolean', $paramRules, true) => 'boolean',
            in_array('array', $paramRules, true) => 'array',
            default => 'string',  //date, ip, email, etc..
        };
    }

    private function isParamRequired(array $paramRules): bool
    {
        return in_array('required', $paramRules, true);
    }

    private function isArrayParameter(string $param): bool
    {
        return Str::contains($param, '*');
    }

    private function getArrayKey(string $param): string
    {
        return current(explode('.', $param));
    }

    private function getEnumValues(array $paramRules): array
    {
        $in = $this->getInParameter($paramRules);

        if (!$in) {
            return [];
        }

        [, $vals] = explode(':', $in);

        return explode(',', $vals);
    }

    private function getInParameter(array $paramRules): ?string
    {
        foreach ($paramRules as $rule) {
            if ((is_string($rule) || method_exists($rule, '__toString')) && Str::startsWith($rule, 'in:')) {
                return $rule;
            }
        }

        return null;
    }
}
