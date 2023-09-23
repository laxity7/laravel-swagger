<?php

namespace Laxity7\LaravelSwagger\Tests\Parsers\Requests\Parameters;

use Illuminate\Routing\Route as LaravelRoute;
use Laxity7\LaravelSwagger\Attributes\Request;
use Laxity7\LaravelSwagger\Parsers\Requests\Generators\BodyParameterGenerator;
use Laxity7\LaravelSwagger\Parsers\Route;
use Laxity7\LaravelSwagger\Tests\Stubs\Requests\BodyParameterRequest;
use Laxity7\LaravelSwagger\Tests\TestCase;

final class BodyParameterGeneratorTest extends TestCase
{
    public function testStructure(): array
    {
        $bodyParameters = $this->getBodyParameters($this->getRoute());

        self::assertContainsAssocArray(['in' => 'body'], $bodyParameters);
        self::assertContainsAssocArray(['name' => 'body'], $bodyParameters);
        self::assertContainsAssocArray(['summary' => 'Get all body parameters.'], $bodyParameters);
        self::assertContainsAssocArray(['description' => 'Use this route to get all body parameters.'], $bodyParameters);
        self::assertArrayHasKey('schema', $bodyParameters);
        self::assertArrayHasKey('type', $bodyParameters['schema']);
        self::assertSame('object', $bodyParameters['schema']['type']);

        return $bodyParameters;
    }

    /**
     * @depends testStructure
     */
    public function testRequiredParameters(array $bodyParameters): void
    {
        self::assertEquals(['id', 'email', 'address', 'dob'], $bodyParameters['schema']['required']);
    }

    /**
     * @depends testStructure
     */
    public function testDataTypes(array $bodyParameters): void
    {
        $properties = $bodyParameters['schema']['properties'];

        self::assertContainsAssocArray(['id' => ['type' => 'integer', 'description' => 'User id']], $properties);
        self::assertContainsAssocArray(['email' => ['type' => 'string', 'description' => 'User email']], $properties);
        self::assertContainsAssocArray(['address' => ['type' => 'string', 'description' => 'User\'s home address']], $properties);
        self::assertContainsAssocArray(['dob' => ['type' => 'string', 'description' => 'User\'s date of birth']], $properties);
        self::assertContainsAssocArray(['picture' => ['type' => 'string', 'description' => 'This is a picture']], $properties);
        self::assertContainsAssocArray(['is_validated' => ['type' => 'boolean', 'description' => 'Is it validated data?']], $properties);
        self::assertContainsAssocArray(['score' => ['type' => 'number', 'description' => 'Score']], $properties);
    }

    /**
     * @depends testStructure
     */
    public function testEnumInBody(array $bodyParameters): void
    {
        self::assertContainsAssocArray([
            'account_type' => [
                'type' => 'integer',
                'enum' => [1, 2],
                'description' => 'Account Type',
            ],
        ], $bodyParameters['schema']['properties']);
    }

    /**
     * @depends testStructure
     */
    public function testArraySyntax(array $bodyParameters): void
    {
        self::assertContainsAssocArray([
            'matrix' => [
                'type' => 'array',
                'items' => [
                    [
                        'type' => 'array',
                        'items' => [
                            [
                                'type' => 'integer',
                            ],
                        ],
                    ],
                ],
                'description' => 'Matrix',
            ],
        ], $bodyParameters['schema']['properties']);
    }

    /**
     * @depends testStructure
     */
    public function testObjectInArraySyntax(array $bodyParameters): void
    {
        self::assertContainsAssocArray([
            'points' => [
                'type' => 'array',
                'items' => [
                    [
                        'type' => 'object',
                        'properties' => [
                            'x' => [
                                'type' => 'number',
                                'description' => 'X',
                            ],
                            'y' => [
                                'type' => 'number',
                                'description' => 'Y',
                            ],
                        ],
                    ],
                ],
                'description' => 'Points',
            ],
        ], $bodyParameters['schema']['properties']);
    }

    /**
     * @depends testStructure
     */
    public function testSingleObjectSyntax(array $bodyParameters): void
    {
        self::assertContainsAssocArray([
            'point' => [
                'type' => 'object',
                'properties' => [
                    'x' => [
                        'type' => 'number',
                        'description' => 'X',
                    ],
                    'y' => [
                        'type' => 'number',
                        'description' => 'Y',
                    ],
                ],
                'description' => 'Point',
            ],
        ], $bodyParameters['schema']['properties']);
    }

    /**
     * @depends testStructure
     */
    public function testResolvesRuleEnum(array $bodyParameters): void
    {
        self::assertContainsAssocArray([
            'type' => [
                'type' => 'integer',
                'enum' => ['"1"', '"2"', '"3"'], //using Rule::in parameters are cast to string
                'description' => 'Type',
            ],
        ], $bodyParameters['schema']['properties']);
    }

    /**
     * @depends testStructure
     */
    public function testIgnoresRuleObject(array $bodyParameters): void
    {
        self::assertContainsAssocArray([
            'name' => [
                'type' => 'string',
                'description' => 'Name',
            ],
        ], $bodyParameters['schema']['properties']);
    }

    /**
     * @depends testStructure
     */
    public function testIgnoresClosureRules(array $bodyParameters): void
    {
        self::assertContainsAssocArray([
            'name_too' => [
                'type' => 'string',
                'description' => 'Name Too',
            ],
        ], $bodyParameters['schema']['properties']);
    }

    private function getRoute(callable $action = null): LaravelRoute
    {
        $action ??= #[Request(BodyParameterRequest::class)] static fn() => '';

        return new LaravelRoute(
            'POST',
            '/',
            $action
        );
    }

    private function getBodyParameters(LaravelRoute $route): array
    {
        return (new BodyParameterGenerator())->getParameters(new Route($route));
    }
}
