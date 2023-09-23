# Laravel Swagger

Laravel Swagger scans your Laravel project's endpoints and auto generates a Swagger 2.0 documentation for you.

[![Build Status](https://travis-ci.org/laxity7/laravel-swagger.svg?branch=master)](https://travis-ci.org/laxity7/laravel-swagger)
[![Latest Stable Version](https://poser.pugx.org/laxity7/laravel-swagger/v/stable)](https://packagist.org/packages/laxity7/laravel-swagger)
[![License](https://poser.pugx.org/laxity7/laravel-swagger/license)](https://packagist.org/packages/laxity7/laravel-swagger)

## About

Laravel Swagger works based on recommended practices by Laravel. It will parse your routes and generate a path object for each one. If you inject Form Request classes in your controller's actions as request validation, it will also generate the parameters for each request that has them. For the parameters, it will take into account wether the request is a GET/HEAD/DELETE or a POST/PUT/PATCH request and make its best guess as to the type of parameter object it should generate. It will also generate the path parameters if your route contains them. Finally, this package will also scan any documentation you have in your action methods and add it as summary and description to that path, along with any appropriate annotations such as @deprecated.

One thing to note is this library leans on being explicit. It will choose to include keys even if they have a default. For example it chooses to say a route has a deprecated value of false rather than leaving it out. I believe this makes reading the documentation easier by not leaving important information out. The file can be easily cleaned up afterwards if the user chooses to leave out the defaults.

## Installation

The package can easily be installed by running `composer require laxity7/laravel-swagger` in your project's root folder.

This will register the artisan command that will be available to you.

You can also override the default config provided by the application by running `php artisan vendor:publish --provider "Laxity7\LaravelSwagger\SwaggerServiceProvider"` in your projects root and change the configuration in the new `config/laravel-swagger.php` file created.

## Usage

Generating the swagger documentation is easy, simply run `php artisan laravel-swagger:generate` in your project root. Keep in mind the command will simply print out the output in your console. If you want the docs saved in a file you can reroute the output like so: `php artisan laravel-swagger:generate > swagger.json`

If you wish to generate docs for a subset of your routes, you can pass a filter using `--filter`, for example: `php artisan laravel-swagger:generate --filter="/api"`

By default, laravel-swagger prints out the documentation in json format, if you want it in YAML format you can override the format using the `--format` flag. Make sure to have the yaml extension installed if you choose to do so.

Format options are:<br>
`json`<br>
`yaml`

## Example

Say you have a route `/api/users/{id}` that maps to `UserController@show`

Your sample controller might look like this:
```php
/**
 * Return all the details of a user
 *
 * Returns the user's first name, last name and address
 * Please see the documentation [here](https://example.com/users) for more information
 *
 * @deprecated
 */
class UserController extends Controller
{
    public function show(UserShowRequest $request, $id)
    {
        return User::find($id);
    }
}
```

And the FormRequest class might look like this:
```php
class UserShowRequest extends FormRequest
{
    public function rules()
    {
        return [
            'fields' => 'array'
            'show_relationships' => 'boolean|required'
        ];
    }
}

```

Running `php artisan swagger:generate > swagger.json` will generate the following file:
```json
{
    "swagger": "2.0",
    "info": {
        "title": "Laravel",
        "description": "Test",
        "version": "1.0.1"
    },
    "host": "localhost:80",
    "basePath": "/",
    "paths": {
        "/api/user/{id}": {
            "get": {
                "summary": "Return all the details of a user",
                "description": "Returns the user's first name, last name and address Please see the documentation [here](https://example.com/users) for more information",
                "deprecated": true,
                "responses": {
                    "200": {
                        "description": "OK"
                    }
                },
                "parameters": [
                    {
                        "in": "path",
                        "name": "id",
                        "type": "integer",
                        "required": true,
                        "description": ""
                    },
                    {
                        "in": "query",
                        "name": "fields",
                        "type": "array",
                        "required": false,
                        "description": ""
                    },
                    {
                        "in": "query",
                        "name": "show_relationships",
                        "type": "boolean",
                        "required": true,
                        "description": ""
                    }
                ]
            },
            ...
        }
    }
}
```
