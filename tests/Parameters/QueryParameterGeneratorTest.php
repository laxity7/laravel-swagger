<?php

namespace Mtrajano\LaravelSwagger\Tests\Parameters;

use Illuminate\Validation\Rule;
use Mtrajano\LaravelSwagger\Parameters\QueryParameterGenerator;
use Mtrajano\LaravelSwagger\Tests\TestCase;

final class QueryParameterGeneratorTest extends TestCase
{
    public function testRequiredParameter(): void
    {
        $queryParameters = $this->getQueryParameters([
            'id' => 'integer|required',
        ]);

        $this->assertSame('query', $queryParameters[0]['in']);
        $this->assertSame('integer', $queryParameters[0]['type']);
        $this->assertSame('id', $queryParameters[0]['name']);
        $this->assertTrue($queryParameters[0]['required']);
    }

    public function testRulesAsArray(): void
    {
        $queryParameters = $this->getQueryParameters([
            'id' => ['integer', 'required'],
        ]);

        $this->assertSame('query', $queryParameters[0]['in']);
        $this->assertSame('integer', $queryParameters[0]['type']);
        $this->assertSame('id', $queryParameters[0]['name']);
        $this->assertTrue($queryParameters[0]['required']);
    }

    public function testOptionalParameter(): void
    {
        $queryParameters = $this->getQueryParameters([
            'email' => 'email',
        ]);

        $this->assertSame('string', $queryParameters[0]['type']);
        $this->assertSame('email', $queryParameters[0]['name']);
        $this->assertFalse($queryParameters[0]['required']);
    }

    public function testEnumInQuery(): void
    {
        $queryParameters = $this->getQueryParameters([
            'account_type' => 'integer|in:1,2|in_array:foo',
        ]);

        $this->assertSame('integer', $queryParameters[0]['type']);
        $this->assertSame('account_type', $queryParameters[0]['name']);
        $this->assertSame(['1', '2'], $queryParameters[0]['enum']);
    }

    public function testEnumRuleObjet(): void
    {
        $queryParameters = $this->getQueryParameters([
            'account_type' => [
                'integer',
                Rule::in(1, 2),
                'in_array:foo',
            ],
        ]);

        $this->assertSame('integer', $queryParameters[0]['type']);
        $this->assertSame('account_type', $queryParameters[0]['name']);
        $this->assertSame(['"1"', '"2"'], $queryParameters[0]['enum']); //using Rule::in parameters are cast to string
    }

    public function testArrayTypeDefaultsToString(): void
    {
        $queryParameters = $this->getQueryParameters([
            'values' => 'array',
        ]);

        $this->assertSame('array', $queryParameters[0]['type']);
        $this->assertSame('values', $queryParameters[0]['name']);
        $this->assertSame(['type' => 'string'], $queryParameters[0]['items']);
        $this->assertFalse($queryParameters[0]['required']);
    }

    public function testArrayValidationSyntax(): void
    {
        $queryParameters = $this->getQueryParameters([
            'values.*' => 'integer',
        ]);

        $this->assertSame('array', $queryParameters[0]['type']);
        $this->assertSame('values', $queryParameters[0]['name']);
        $this->assertSame(['type' => 'integer'], $queryParameters[0]['items']);
        $this->assertFalse($queryParameters[0]['required']);
    }

    public function testArrayValidationSyntaxWithRequiredArray(): void
    {
        $queryParameters = $this->getQueryParameters([
            'values.*' => 'integer',
            'values' => 'required',
        ]);

        $this->assertSame('array', $queryParameters[0]['type']);
        $this->assertSame('values', $queryParameters[0]['name']);
        $this->assertSame(['type' => 'integer'], $queryParameters[0]['items']);
        $this->assertTrue($queryParameters[0]['required']);
    }

    private function getQueryParameters(array $rules): array
    {
        return (new QueryParameterGenerator($rules))->getParameters();
    }
}
