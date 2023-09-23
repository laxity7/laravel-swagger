<?php

namespace Laxity7\LaravelSwagger\Parsers\Requests\Generators;

use Laxity7\LaravelSwagger\Enums\Method;
use Laxity7\LaravelSwagger\Parsers\Requests\RulesParamHelper;
use Laxity7\LaravelSwagger\Parsers\Route;

final class BodyParameterGenerator implements ParameterGenerator
{
    private function getParametersFromRules(Route $route): array
    {
        $rules = $route->request?->getRules() ?? [];

        $required = [];
        $properties = [];
        foreach ($rules as $param => $rule) {
            $paramRules = RulesParamHelper::splitRules($rule);
            $nameTokens = explode('.', $param);
            $properties = $this->addToProperties($route, $nameTokens, $paramRules, $properties);

            if (RulesParamHelper::isParamRequired($paramRules)) {
                $required[] = $param;
            }
        }

        return [$properties, $required];
    }

    public function getParameters(Route $route): array
    {
        [$properties, $required] = $this->getParametersFromRules($route);

        $params = [
            'in' => 'body',
            'name' => 'body',
            'summary' => $route->request?->getSummary() ?? '',
            'description' => $route->request?->getDescription() ?? '',
            'schema' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => $required,
            ],
        ];

        if (empty($required)) {
            unset($params['schema']['required']);
        }

        return [$params];
    }

    private function addToProperties(Route $route, array $nameTokens, array $rules, array $properties = []): array
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
            if ($name !== 0) {
                $properties[$name]['description'] = $route->request->getFieldDescription($name);
            }
        } else {
            $properties[$name]['type'] = $type;
        }

        if ($type === 'array') {
            $properties[$name]['items'] = $this->addToProperties($route, $nameTokens, $rules, $properties[$name]['items'] ?? []);
        } elseif ($type === 'object') {
            $properties[$name]['properties'] = $this->addToProperties($route, $nameTokens, $rules, $properties[$name]['properties'] ?? []);
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

        $enums = RulesParamHelper::getEnumValues($rules);
        if ($enums) {
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
