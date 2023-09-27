<?php

use Laxity7\LaravelSwagger\Formatters\JsonFormatter;
use Laxity7\LaravelSwagger\Formatters\YamlFormatter;
use Laxity7\LaravelSwagger\Parsers\Requests\Parameters\BodyParameterParser;
use Laxity7\LaravelSwagger\Parsers\Requests\Parameters\PathParameterParser;
use Laxity7\LaravelSwagger\Parsers\Requests\Parameters\QueryParameterParser;

return [

    /*
    |--------------------------------------------------------------------------
    | Basic Info
    |--------------------------------------------------------------------------
    |
    | The basic info for the application such as the title description,
    | description, version, etc...
    |
    */

    'title' => env('APP_NAME'),

    'description' => '',

    'appVersion' => '1.0.0',

    'host' => env('APP_URL'),

    'basePath' => '/',

    'schemes' => [
        // 'http',
        // 'https',
    ],

    'consumes' => [
        // 'application/json',
    ],

    'produces' => [
        // 'application/json',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore methods
    |--------------------------------------------------------------------------
    |
    | Methods in the following array will be ignored in the paths array
    |
    */

    'ignoredMethods' => [
        'head',
    ],

    /**
     * If you wish to generate docs for a subset of your routes, you can pass a filter
     * for example "/api"
     */
    'routeFilter' => null,

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    |
    | If your application uses Laravel's Passport package with the recommended
    | settings, Laravel Swagger will attempt to parse your settings and
    | automatically generate the securityDefinitions along with the operation
    | object's security parameter, you may turn off this behavior with parseSecurity.
    |
    | Possible values for flow: ["implicit", "password", "application", "accessCode"]
    | See https://medium.com/@darutk/diagrams-and-movies-of-all-the-oauth-2-0-flows-194f3c3ade85
    | for more information.
    |
    */

    'parseSecurity' => true,

    'authFlow' => 'accessCode',

    /*
    |--------------------------------------------------------------------------
    | Overrides
    |--------------------------------------------------------------------------
    |
    | Allow overriding generator classes with a custom implementation
    */
    'formatters' => [
        'json' => JsonFormatter::class,
        'yaml' => YamlFormatter::class,
    ],

    'parameterParsers' => [
        PathParameterParser::class,
        BodyParameterParser::class,
        QueryParameterParser::class,
    ],
];
