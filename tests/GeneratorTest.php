<?php

namespace Mtrajano\LaravelSwagger\Tests;

use Mtrajano\LaravelSwagger\Generator;
use Mtrajano\LaravelSwagger\LaravelSwaggerException;

final class GeneratorTest extends TestCase
{
    private const ENDPOINTS = [
        '/users',
        '/users/{id}',
        '/users/{id}/details',
        '/users/{id}/details/{detail_id}',
        '/users/{foo}',
        '/users/ping',
        '/api',
        '/api/store',
        '/oauth/token',
        '/oauth/authorize',
        '/oauth/token/refresh',
        '/oauth/tokens',
        '/oauth/tokens/{token_id}',
        '/oauth/clients',
        '/oauth/clients/{client_id}',
        '/oauth/scopes',
        '/oauth/personal-access-tokens',
        '/oauth/personal-access-tokens/{token_id}',
    ];
    private array $config;
    private Generator $generator;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = config('laravel-swagger');
        $this->generator = new Generator($this->config);
    }

    public function testRequiredBaseInfo(): array
    {
        $docs = $this->generator->generate();

        $this->assertArrayHasKey('swagger', $docs);
        $this->assertArrayHasKey('info', $docs);
        $this->assertArrayHasKey('title', $docs['info']);
        $this->assertArrayHasKey('description', $docs['info']);
        $this->assertArrayHasKey('version', $docs['info']);
        $this->assertArrayHasKey('host', $docs);
        $this->assertArrayHasKey('basePath', $docs);
        $this->assertArrayHasKey('paths', $docs);

        return $docs;
    }

    public function testRequiredBaseInfoData(): void
    {
        $docs = $this->getDocsWithNewConfig([
            'title' => 'My awesome site!',
            'description' => 'This is my awesome site, please enjoy it',
            'appVersion' => '1.0.0',
            'host' => 'https://example.com',
            'basePath' => '/api',
            'schemes' => [
                'https',
            ],
            'consumes' => [
                'application/json',
            ],
            'produces' => [
                'application/json',
            ],
        ]);

        $this->assertSame('2.0', $docs['swagger']);
        $this->assertSame('My awesome site!', $docs['info']['title']);
        $this->assertSame('This is my awesome site, please enjoy it', $docs['info']['description']);
        $this->assertSame('1.0.0', $docs['info']['version']);
        $this->assertSame('https://example.com', $docs['host']);
        $this->assertSame('/api', $docs['basePath']);
        $this->assertSame(['https'], $docs['schemes']);
        $this->assertSame(['application/json'], $docs['consumes']);
        $this->assertSame(['application/json'], $docs['produces']);
    }

    public function testSecurityDefinitionsAccessCodeFlow(): void
    {
        $docs = $this->getDocsWithNewConfig([
            'authFlow' => 'accessCode',
        ]);

        $this->assertArrayHasKey('securityDefinitions', $docs);

        $securityDefinition = $docs['securityDefinitions']['OAuth2'];

        $this->assertSame('oauth2', $securityDefinition['type']);
        $this->assertSame('accessCode', $securityDefinition['flow']);
        $this->assertArrayHasKey('user-read', $securityDefinition['scopes']);
        $this->assertArrayHasKey('user-write', $securityDefinition['scopes']);
        $this->assertArrayHasKey('authorizationUrl', $securityDefinition);
        $this->assertArrayHasKey('tokenUrl', $securityDefinition);
    }

    public function testSecurityDefinitionsImplicitFlow(): void
    {
        $docs = $this->getDocsWithNewConfig([
            'authFlow' => 'implicit',
        ]);

        $securityDefinition = $docs['securityDefinitions']['OAuth2'];

        $this->assertSame('oauth2', $securityDefinition['type']);
        $this->assertSame('implicit', $securityDefinition['flow']);
        $this->assertArrayHasKey('authorizationUrl', $securityDefinition);
        $this->assertArrayNotHasKey('tokenUrl', $securityDefinition);
    }

    public function testSecurityDefinitionsPasswordFlow(): void
    {
        $docs = $this->getDocsWithNewConfig([
            'authFlow' => 'password',
        ]);

        $securityDefinition = $docs['securityDefinitions']['OAuth2'];

        $this->assertSame('oauth2', $securityDefinition['type']);
        $this->assertSame('password', $securityDefinition['flow']);
        $this->assertArrayNotHasKey('authorizationUrl', $securityDefinition);
        $this->assertArrayHasKey('tokenUrl', $securityDefinition);
    }

    public function testSecurityDefinitionsApplicationFlow(): void
    {
        $docs = $this->getDocsWithNewConfig([
            'authFlow' => 'application',
        ]);

        $securityDefinition = $docs['securityDefinitions']['OAuth2'];

        $this->assertSame('oauth2', $securityDefinition['type']);
        $this->assertSame('application', $securityDefinition['flow']);
        $this->assertArrayNotHasKey('authorizationUrl', $securityDefinition);
        $this->assertArrayHasKey('tokenUrl', $securityDefinition);
    }

    public function testNoParseSecurity(): void
    {
        $docs = $this->getDocsWithNewConfig([
            'parseSecurity' => false,
        ]);

        $this->assertArrayNotHasKey('securityDefinitions', $docs);
    }

    public function testInvalidFlowPassed(): void
    {
        $this->expectException(LaravelSwaggerException::class);

        $this->getDocsWithNewConfig([
            'authFlow' => 'invalidFlow',
        ]);
    }

    /**
     * @depends testRequiredBaseInfo
     */
    public function testHasPaths(array $docs): array
    {
        $this->assertSame(self::ENDPOINTS, array_keys($docs['paths']));

        return $docs['paths'];
    }

    /**
     * @depends testHasPaths
     */
    public function testPathMethods(array $paths): void
    {
        $this->assertArrayHasKey('get', $paths['/users']);
        $this->assertArrayNotHasKey('head', $paths['/users']);
        $this->assertArrayHasKey('post', $paths['/users']);

        $this->assertArrayHasKey('get', $paths['/users/{id}']);

        $this->assertArrayHasKey('get', $paths['/users/{id}/details']);
    }

    /**
     * @depends testHasPaths
     */
    public function testRouteData(array $paths): void
    {
        $expectedPostDescription = <<<'EOD'
Data is validated [see description here](https://example.com) so no bad data can be passed.
Please read the documentation for more information
EOD;

        $this->assertArrayHasKey('summary', $paths['/users']['get']);
        $this->assertArrayHasKey('description', $paths['/users']['get']);
        $this->assertArrayHasKey('responses', $paths['/users']['get']);
        $this->assertArrayNotHasKey('deprecated', $paths['/users']['get']);
        $this->assertArrayNotHasKey('parameters', $paths['/users']['get']);

        $this->assertSame('Get a list of of users in the application', $paths['/users']['get']['summary']);
        $this->assertSame('', $paths['/users']['get']['description']);

        $this->assertSame('Store a new user in the application', $paths['/users']['post']['summary']);
        $this->assertTrue($paths['/users']['post']['deprecated']);
        $this->assertSame($expectedPostDescription, $paths['/users']['post']['description']);

        $this->assertSame('', $paths['/users/{id}']['get']['summary']);
        $this->assertArrayNotHasKey('deprecated', $paths['/users/{id}']['get']);
        $this->assertSame('', $paths['/users/{id}']['get']['description']);

        $this->assertSame('', $paths['/users/{id}/details']['get']['summary']);
        $this->assertTrue($paths['/users/{id}/details']['get']['deprecated']);
        $this->assertSame('', $paths['/users/{id}/details']['get']['description']);
    }

    /**
     * @depends testHasPaths
     */
    public function testRouteScopes(array $paths): void
    {
        $this->assertSame(['user-read'], $paths['/users']['get']['security'][Generator::SECURITY_DEFINITION_NAME]);
        $this->assertSame(['user-read', 'user-write'], $paths['/users']['post']['security'][Generator::SECURITY_DEFINITION_NAME]);
    }

    public function testOverwriteIgnoreMethods(): void
    {
        $docs = $this->getDocsWithNewConfig(['ignoredMethods' => []]);

        $this->assertArrayHasKey('head', $docs['paths']['/users']);
    }

    public function testParseDocBlockFalse(): void
    {
        $docs = $this->getDocsWithNewConfig(['parseDocBlock' => false]);

        $this->assertSame('', $docs['paths']['/users']['post']['summary']);
        $this->assertArrayNotHasKey('deprecated', $docs['paths']['/users']['post']);
        $this->assertSame('', $docs['paths']['/users']['post']['description']);
    }

    public function testOptionalData(): void
    {
        $docs = $this->getDocsWithNewConfig([
            'schemes' => [
                'http',
                'https',
            ],

            'consumes' => [
                'application/json',
            ],

            'produces' => [
                'application/json',
            ],
        ]);

        $this->assertArrayHasKey('schemes', $docs);
        $this->assertArrayHasKey('consumes', $docs);
        $this->assertArrayHasKey('produces', $docs);

        $this->assertContains('http', $docs['schemes']);
        $this->assertContains('https', $docs['schemes']);
        $this->assertContains('application/json', $docs['consumes']);
        $this->assertContains('application/json', $docs['produces']);
    }

    /**
     * @dataProvider filtersRoutesProvider
     */
    public function testFiltersRoutes(?string $routeFilter, array $expectedRoutes): void
    {
        $this->generator = new Generator([...$this->config, 'routeFilter' => $routeFilter]);

        $docs = $this->generator->generate();

        $this->assertSame($expectedRoutes, array_keys($docs['paths']));
    }

    /**
     * @return array
     */
    public static function filtersRoutesProvider(): array
    {
        return [
            'No Filter' => [null, self::ENDPOINTS],
            '/api Filter' => ['/api', ['/api', '/api/store']],
            '/=nonexistant Filter' => ['/nonexistant', []],
        ];
    }

    private function getDocsWithNewConfig(array $config): array
    {
        $config = array_merge($this->config, $config);

        return (new Generator($config))->generate();
    }
}
