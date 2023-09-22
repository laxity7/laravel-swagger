<?php

namespace Mtrajano\LaravelSwagger;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Route as LaravelRoute;
use Laravel\Passport\Passport;
use Mtrajano\LaravelSwagger\Enums\Method;
use Mtrajano\LaravelSwagger\Parsers\MethodParser;
use Mtrajano\LaravelSwagger\Parsers\Requests\Generators\ParameterGenerator;
use Mtrajano\LaravelSwagger\Parsers\Requests\RequestParser;
use Mtrajano\LaravelSwagger\Parsers\ResponseParser;
use Mtrajano\LaravelSwagger\Parsers\Route;

final class Generator implements GeneratorContract
{
    public const SECURITY_DEFINITION_NAME = 'OAuth2';
    public const OAUTH_TOKEN_PATH = '/oauth/token';
    public const OAUTH_AUTHORIZE_PATH = '/oauth/authorize';

    public const TAG_IGNORE = 'ignore';
    public const TAG_REQUEST = 'request';
    public const TAG_RESPONSE = 'response';

    private string|null $routeFilter;
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
         *     parseSecurity: bool,
         *     authFlow: string,
         *     generatorClass: string,
         *     requestsGenerators: list<class-string<ParameterGenerator>>,
         * }
         */
        private readonly array $config
    ) {
        $this->routeFilter = $config['routeFilter'] ?? null;
    }

    public function setRouteFilter(string $routeFilter): self
    {
        $this->routeFilter = $routeFilter;

        return $this;
    }

    private function disableValidation(): void
    {
        (function () {
            /** @var Application $this */
            unset($this->afterResolvingCallbacks['Illuminate\Contracts\Validation\ValidatesWhenResolved']);
        })->call(app());
    }

    public function generate(): array
    {
        $this->disableValidation();

        $docs = $this->getBaseInfo();

        if ($this->config['parseSecurity'] && $this->hasOauthRoutes()) {
            $docs['securityDefinitions'] = $this->generateSecurityDefinitions();
            $this->hasSecurityDefinitions = true;
        }

        $ignoredMethods = Method::fromArray($this->config['ignoredMethods']);
        $paths = [];
        foreach ($this->getAppRoutes() as $route) {
            if ($this->needSkipRoute($route)) {
                continue;
            }
            $paths[$route->uri()] ??= [];

            foreach ($route->methods() as $method) {
                if ($method->isOneOf(...$ignoredMethods)) {
                    continue;
                }

                $methodParser = new MethodParser(
                    route: $route,
                    requestParser: new RequestParser($route, $method, $this->config['requestsGenerators']),
                    responseParser: new ResponseParser($route, $method),
                    hasSecurityDefinitions: $this->hasSecurityDefinitions,
                );
                if ($methodParser->isSkipped()) {
                    continue;
                }
                $paths[$route->uri()][$method->lowerValue()] = $methodParser->parse()->toArray();
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
        return array_map(
            fn(LaravelRoute $route) => new Route($route),
            app('router')->getRoutes()->getRoutes()
        );
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


    private function needSkipRoute(Route $route): bool
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
