<?php

namespace Laxity7\LaravelSwagger\Parsers\Requests\Parameters;

use Illuminate\Support\Str;
use Laxity7\LaravelSwagger\Enums\Method;
use Laxity7\LaravelSwagger\Parsers\Requests\EnumExtractor;
use Laxity7\LaravelSwagger\Parsers\Route;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use UnitEnum;

final class PathParameterParser implements ParameterParser
{
    private Route $route;

    public function getParameters(Route $route): array
    {
        $this->route = $route;

        $parameters = [];
        foreach ($this->route->pathParameterNames() as $variable) {
            $parameters[] = [
                'in' => 'path',
                'name' => $variable,
                'schema' => $this->getType($variable),
                'required' => $this->isPathParameterRequired($variable),
                'description' => $this->getDescription($variable),
            ];
        }

        return $parameters;
    }

    private function getDescription(string $paramName): string
    {
        $default = Str::headline($paramName);
        $docBlock = $this->route->getMethodDocBlock();
        if ($docBlock === null || !$docBlock->hasTag('param')) {
            return $default;
        }
        /** @var Param $param */
        foreach ($docBlock->getTagsByName('param') as $param) {
            if ($param->getVariableName() === $paramName) {
                return $param->getDescription()?->render() ?? $default;
            }
        }

        return $default;
    }

    /**
     * @param  string  $paramName
     * @return array{type: string, pattern?: string, default?: mixed, format?: string, enum: array}
     */
    private function getType(string $paramName): array
    {
        $type = ['type' => 'string'];
        if ($this->hasPattern($paramName)) {
            $type['pattern'] = $this->getPattern($paramName);
        }
        if ($this->hasDefault($paramName)) {
            $type['default'] = $this->getDefault($paramName);
        }

        foreach ($this->route->getParameters() as $parameter) {
            if ($parameter->getName() !== $paramName) {
                continue;
            }

            if ($parameter->getType()?->getName()) {
                $type['type'] = $parameter->getType()?->getName();
            }
            if ($parameter->isDefaultValueAvailable()) {
                $type['default'] = $parameter->getDefaultValue();
            }

            break;
        }

        return $this->convertType($type);
    }

    /**
     * Convert a PHP type to a Swagger type
     * @param  array{type: string, pattern?: string, default?: mixed}  $type
     * @return array{type: string, pattern?: string, default?: mixed, format?: string, enum: array}
     */
    private function convertType(array $type): array
    {
        $type['type'] = match ($type['type']) {
            'int' => 'integer',
            'float', 'double' => 'number',
            'bool' => 'boolean',
            default => $type['type'],
        };

        if (enum_exists($type['type'])) {
            $enum = new EnumExtractor($type['type']);
            $type['enum'] = $enum->getValues();
            $type['type'] = $enum->getType();
            if (isset($type['default']) && $type['default'] instanceof UnitEnum) {
                $type['default'] = EnumExtractor::getValue($type['default']);
            }

            return $this->convertType($type);
        }

        if ($type['type'] !== 'string' || !isset($type['pattern'])) {
            return $type;
        }

        if ($type['pattern'] === '[\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}') {
            unset($type['pattern']);
            $type['format'] = 'uuid';

            return $type;
        }

        if (preg_match('/^(\w+\|){2,}$/', $type['pattern'].'|')) {
            $type['enum'] = explode('|', $type['pattern']);
            unset($type['pattern']);

            return $type;
        }

        return $type;
    }

    private function hasPattern(string $paramName): bool
    {
        return isset($this->route->route->wheres[$paramName]);
    }

    private function getPattern(string $paramName): string
    {
        return $this->route->route->wheres[$paramName];
    }

    private function hasDefault(string $paramName): bool
    {
        return isset($this->route->route->defaults[$paramName]);
    }

    private function getDefault(string $paramName): mixed
    {
        return $this->route->route->defaults[$paramName];
    }

    private function isPathParameterRequired(string $pathVariable): bool
    {
        return !Str::contains($this->route->originalUri(), $pathVariable.'?');
    }

    public function isNeedParsing(Route $route, Method $method): bool
    {
        return true;
    }
}
