<?php

namespace Mtrajano\LaravelSwagger;

use Illuminate\Routing\Route as LaravelRoute;
use Laravel\Passport\Passport;
use Mtrajano\LaravelSwagger\DataObjects\Route;
use phpDocumentor\Reflection\DocBlockFactory;

final class Generator implements GeneratorContract
{
    public const SECURITY_DEFINITION_NAME = 'OAuth2';
    public const OAUTH_TOKEN_PATH = '/oauth/token';
    public const OAUTH_AUTHORIZE_PATH = '/oauth/authorize';

    private string|null $routeFilter;
    private DocBlockFactory $docParser;
    private bool $hasSecurityDefinitions = false;

    public function __construct(
        /**
         * @var array{
         *     title: string,
         *     description: string,
         *     appVersion: string,
         *     host: string,
         *     basePath: string,
         *     schemes: string[],
         *     consumes: string[],
         *     produces: string[],
         *     ignoredMethods: string[],
         *     routeFilter: string|null,
         *     parseDocBlock: bool,
         *     parseSecurity: bool,
         *     authFlow: string,
         *     generatorClass: string,
         * }
         */
        private readonly array $config
    ) {
        $this->routeFilter = $config['routeFilter'] ?? null;
        $this->docParser = DocBlockFactory::createInstance();
    }

    public function setRouteFilter(string $routeFilter): self
    {
        $this->routeFilter = $routeFilter;

        return $this;
    }

    public function generate(): array
    {
        $docs = $this->getBaseInfo();

        if ($this->config['parseSecurity'] && $this->hasOauthRoutes()) {
            $docs['securityDefinitions'] = $this->generateSecurityDefinitions();
            $this->hasSecurityDefinitions = true;
        }

        $paths = [];
        foreach ($this->getAppRoutes() as $route) {
            if ($this->isFilteredRoute($route)) {
                continue;
            }
            $paths[$route->uri()] ??= [];

            foreach ($route->methods() as $method) {
                if (in_array($method, $this->config['ignoredMethods'], true)) {
                    continue;
                }

                $routeGenerator = new MethodParser(
                    route: $route,
                    methodName: $method,
                    hasSecurityDefinitions: $this->hasSecurityDefinitions,
                    parseDocBlock: $this->config['parseDocBlock'],
                    docParser: $this->docParser,
                );
                $paths[$route->uri()][$method] = $routeGenerator->parse()->toArray();
            }
        }
        $docs['paths'] = $paths;

        return $docs;
    }

    private function getBaseInfo(): array
    {
        $baseInfo = [
            'swagger' => '2.0',
            'info' => [
                'title' => $this->config['title'],
                'description' => $this->config['description'],
                'version' => $this->config['appVersion'],
            ],
            'host' => $this->config['host'],
            'basePath' => $this->config['basePath'],
        ];

        if (!empty($this->config['schemes'])) {
            $baseInfo['schemes'] = $this->config['schemes'];
        }

        if (!empty($this->config['consumes'])) {
            $baseInfo['consumes'] = $this->config['consumes'];
        }

        if (!empty($this->config['produces'])) {
            $baseInfo['produces'] = $this->config['produces'];
        }

        $baseInfo['paths'] = [];

        return $baseInfo;
    }

    /**
     * @return Route[]
     */
    private function getAppRoutes(): array
    {
        return array_map(fn(LaravelRoute $route) => new Route($route), app('router')->getRoutes()->getRoutes());
    }

    private function generateSecurityDefinitions(): array
    {
        $authFlow = $this->config['authFlow'];

        $this->validateAuthFlow($authFlow);

        $securityDefinition = [
            self::SECURITY_DEFINITION_NAME => [
                'type' => 'oauth2',
                'flow' => $authFlow,
            ],
        ];

        if (in_array($authFlow, ['implicit', 'accessCode'])) {
            $securityDefinition[self::SECURITY_DEFINITION_NAME]['authorizationUrl'] = $this->getEndpoint(self::OAUTH_AUTHORIZE_PATH);
        }

        if (in_array($authFlow, ['password', 'application', 'accessCode'])) {
            $securityDefinition[self::SECURITY_DEFINITION_NAME]['tokenUrl'] = $this->getEndpoint(self::OAUTH_TOKEN_PATH);
        }

        $securityDefinition[self::SECURITY_DEFINITION_NAME]['scopes'] = $this->generateOauthScopes();

        return $securityDefinition;
    }


    private function isFilteredRoute(Route $route): bool
    {
        return $this->routeFilter && !preg_match('/^'.preg_quote($this->routeFilter, '/').'/', $route->uri());
    }

    /**
     * Assumes routes have been created using Passport::routes().
     */
    private function hasOauthRoutes(): bool
    {
        foreach ($this->getAppRoutes() as $route) {
            $uri = $route->uri();

            if ($uri === self::OAUTH_TOKEN_PATH || $uri === self::OAUTH_AUTHORIZE_PATH) {
                return true;
            }
        }

        return false;
    }

    private function getEndpoint(string $path): string
    {
        return rtrim($this->config['host'], '/').$path;
    }

    private function generateOauthScopes(): array
    {
        if (!class_exists(Passport::class)) {
            return [];
        }

        $scopes = Passport::scopes()->toArray();

        return array_combine(array_column($scopes, 'id'), array_column($scopes, 'description'));
    }

    /**
     * @throws LaravelSwaggerException
     */
    private function validateAuthFlow(string $flow): void
    {
        if (!in_array($flow, ['password', 'application', 'implicit', 'accessCode'])) {
            throw new LaravelSwaggerException('Invalid OAuth flow passed');
        }
    }
}
