<?php

namespace Mtrajano\LaravelSwagger\Parsers\Requests\Generators;

use Mtrajano\LaravelSwagger\DataObjects\Route;
use Mtrajano\LaravelSwagger\Enums\Method;
use Mtrajano\LaravelSwagger\Parsers\Requests\RulesParamHelper;

final class BodyParameterGenerator implements ParameterGenerator
{
    public function getParametersFromRules(array $rules): array
    {
        $required = [];
        $properties = [];
        foreach ($rules as $param => $rule) {
            $paramRules = RulesParamHelper::splitRules($rule);
            $nameTokens = explode('.', $param);

            $properties = $this->addToProperties($nameTokens, $paramRules, $properties);

            if (RulesParamHelper::isParamRequired($paramRules)) {
                $required[] = $param;
            }
        }

        $params = [
            'in' => 'body',
            'name' => 'body',
            'description' => '',
            'schema' => [
                'type' => 'object',
                'properties' => $properties
            ],
        ];

        if (!empty($required)) {
            $params['schema']['required'] = $required;
        }

        return [$params];
    }

    public function getParameters(Route $route): array
    {
        return $this->getParametersFromRules(RulesParamHelper::getFormRules($route));
    }

    private function addToProperties(array $nameTokens, array $rules, array $properties = []): array
    {
        if (empty($nameTokens)) {
            return [];
        }

        $name = array_shift($nameTokens);
        if ($name === '*') {
            $name = 0;
        }

        if (!empty($nameTokens)) {
            $type = $this->getNestedParamType($nameTokens);
        } else {
            $type = RulesParamHelper::getParamType($rules);
        }

        if (!isset($properties[$name])) {
            $properties[$name] = $this->getNewPropObj($type, $rules);
        } else {
            //overwrite previous type in case it wasn't given before
            $properties[$name]['type'] = $type;
        }

        if ($type === 'array') {
            $properties[$name]['items'] = $this->addToProperties($nameTokens, $rules, $properties[$name]['items'] ?? []);
        } elseif ($type === 'object') {
            $properties[$name]['properties'] = $this->addToProperties($nameTokens, $rules, $properties[$name]['properties'] ?? []);
        }

        return $properties;
    }

    private function getNestedParamType(array $nameTokens): string
    {
        if (current($nameTokens) === '*') {
            return 'array';
        }

        return 'object';
    }

    private function getNewPropObj(string $type, array $rules): array
    {
        $propObj = [
            'type' => $type,
        ];

        if ($enums = RulesParamHelper::getEnumValues($rules)) {
            $propObj['enum'] = $enums;
        }

        if ($type === 'array') {
            $propObj['items'] = [];
        } elseif ($type === 'object') {
            $propObj['properties'] = [];
        }

        return $propObj;
    }

    public function isNeedParsing(Route $route, Method $method): bool
    {
        return $method->isOneOf(Method::POST, Method::PUT, Method::PATCH);
    }
}
