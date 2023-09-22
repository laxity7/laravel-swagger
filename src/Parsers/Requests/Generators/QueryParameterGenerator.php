<?php

namespace Laxity7\LaravelSwagger\Parsers\Requests\Generators;

use Laxity7\LaravelSwagger\Enums\Method;
use Laxity7\LaravelSwagger\Parsers\Requests\RulesParamHelper;
use Laxity7\LaravelSwagger\Parsers\Route;

final class QueryParameterGenerator implements ParameterGenerator
{
    public function getParametersFromRules(array $rules): array
    {
        $params = [];
        $arrayTypes = [];

        foreach ($rules as $param => $rule) {
            $paramRules = RulesParamHelper::splitRules($rule);
            $enums = RulesParamHelper::getEnumValues($paramRules);
            $type = RulesParamHelper::getParamType($paramRules);

            if (RulesParamHelper::isArrayParameter($param)) {
                $arrayKey = RulesParamHelper::getArrayKey($param);
                $arrayTypes[$arrayKey] = $type;
                continue;
            }

            $paramObj = [
                'in' => 'query',
                'name' => $param,
                'type' => $type,
                'required' => RulesParamHelper::isParamRequired($paramRules),
                'description' => '',
            ];

            if (!empty($enums)) {
                $paramObj['enum'] = $enums;
            }

            if ($type === 'array') {
                $paramObj['items'] = ['type' => 'string'];
            }

            $params[$param] = $paramObj;
        }

        $params = $this->addArrayTypes($params, $arrayTypes);

        return array_values($params);
    }

    public function getParameters(Route $route): array
    {
        $rules = $route->request?->getRules() ?? [];

        return $this->getParametersFromRules($rules);
    }

    private function addArrayTypes(array $params, array $arrayTypes): array
    {
        foreach ($arrayTypes as $arrayKey => $type) {
            $params[$arrayKey] ??= [
                'in' => 'query',
                'name' => $arrayKey,
                'type' => 'array',
                'required' => false,
                'description' => '',
                'items' => [
                    'type' => $type,
                ],
            ];
            $params[$arrayKey]['type'] = 'array';
            $params[$arrayKey]['items']['type'] = $type;
        }

        return $params;
    }

    public function isNeedParsing(Route $route, Method $method): bool
    {
        return $method->is(Method::GET);
    }
}
