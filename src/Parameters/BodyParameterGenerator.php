<?php

namespace Mtrajano\LaravelSwagger\Parameters;

final class BodyParameterGenerator implements ParameterGenerator
{
    use Concerns\GeneratesFromRules;

    public function __construct(
        readonly private array $rules
    ) {
    }

    public function getParameters(): array
    {
        $required = [];
        $properties = [];
        foreach ($this->rules as $param => $rule) {
            $paramRules = $this->splitRules($rule);
            $nameTokens = explode('.', $param);

            $properties = $this->addToProperties($nameTokens, $paramRules, $properties);

            if ($this->isParamRequired($paramRules)) {
                $required[] = $param;
            }
        }

        $params = [
            'in' => $this->getParamLocation(),
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

    public function getParamLocation(): string
    {
        return 'body';
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
            $type = $this->getParamType($rules);
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

        if ($enums = $this->getEnumValues($rules)) {
            $propObj['enum'] = $enums;
        }

        if ($type === 'array') {
            $propObj['items'] = [];
        } elseif ($type === 'object') {
            $propObj['properties'] = [];
        }

        return $propObj;
    }
}
