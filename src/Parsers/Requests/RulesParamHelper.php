<?php

namespace Laxity7\LaravelSwagger\Parsers\Requests;

use Illuminate\Support\Str;

final class RulesParamHelper
{
    private function __construct()
    {
    }

    public static function splitRules(array|string $rules): array
    {
        if (is_string($rules)) {
            return explode('|', $rules);
        }

        return $rules;
    }

    public static function getParamType(array $paramRules): string
    {
        return match (true) {
            in_array('integer', $paramRules, true) => 'integer',
            in_array('numeric', $paramRules, true) => 'number',
            in_array('boolean', $paramRules, true) => 'boolean',
            in_array('array', $paramRules, true) => 'array',
            default => 'string',  //date, ip, email, etc..
        };
    }

    public static function isParamRequired(array $paramRules): bool
    {
        return in_array('required', $paramRules, true);
    }

    public static function isArrayParameter(string $param): bool
    {
        return Str::contains($param, '*');
    }

    public static function getArrayKey(string $param): string
    {
        return current(explode('.', $param));
    }

    public static function getEnumValues(array $paramRules): array
    {
        $in = self::getInParameter($paramRules);

        if (!$in) {
            return [];
        }

        [, $vals] = explode(':', $in);

        return explode(',', $vals);
    }

    public static function getInParameter(array $paramRules): ?string
    {
        foreach ($paramRules as $rule) {
            if ((is_string($rule) || method_exists($rule, '__toString')) && Str::startsWith($rule, 'in:')) {
                return $rule;
            }
        }

        return null;
    }
}
