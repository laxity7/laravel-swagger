<?php

namespace Mtrajano\LaravelSwagger\Parameters;

use Illuminate\Support\Str;
use function Mtrajano\LaravelSwagger\strip_optional_char;

final class PathParameterGenerator implements ParameterGenerator
{
    public function __construct(
        private readonly string $uri
    ) {
    }

    public function getParameters(): array
    {
        $params = [];
        $pathVariables = $this->getAllVariablesFromUri();

        foreach ($pathVariables as $variable) {
            $params[] = [
                'in' => $this->getParamLocation(),
                'name' => strip_optional_char($variable),
                'type' => 'string', //best guess for a variable in the path
                'required' => $this->isPathVariableRequired($variable),
                'description' => '',
            ];
        }

        return $params;
    }

    private function getAllVariablesFromUri(): array
    {
        preg_match_all('/{(\w+\??)}/', $this->uri, $pathVariables);

        return $pathVariables[1];
    }

    public function getParamLocation(): string
    {
        return 'path';
    }

    private function isPathVariableRequired(string $pathVariable): bool
    {
        return !Str::contains($pathVariable, '?');
    }
}
