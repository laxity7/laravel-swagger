# Laravel Swagger

Laravel Swagger scans your Laravel project's endpoints and auto generates a Swagger 2.0 documentation for you.

[![Total Downloads](https://img.shields.io/packagist/dt/laxity7/dotenv.svg)](https://packagist.org/packages/laxity7/laravel-swagger)
[![Latest Stable Version](https://poser.pugx.org/laxity7/laravel-swagger/v/stable)](https://packagist.org/packages/laxity7/laravel-swagger)
[![License](https://poser.pugx.org/laxity7/laravel-swagger/license)](https://packagist.org/packages/laxity7/laravel-swagger)

## About

Laravel Swagger works based on recommended practices by Laravel. It will parse your routes and generate a path object for each one.
If you inject Form Request classes in your controller's actions as request validation, it will also generate the parameters for each request that has them.
For the parameters, it will take into account whether the request is a GET/HEAD/DELETE or a POST/PUT/PATCH request and make its best guess as to the type of
parameter object it should generate.
It will also generate the path parameters if your route contains them. Finally, this package will also scan any documentation you have in your action methods
and add it as summary and description to that path, along with any appropriate annotations such as @deprecated.

One thing to note is this library leans on being explicit.
It will choose to include keys even if they have a default.
For example, it chooses to say a route has a deprecated value of false rather than leaving it out.
The file can be easily cleaned up afterward if the user chooses to leave out the defaults.

## Installation

The package can easily be installed by running `composer require laxity7/laravel-swagger` in your project's root folder.

This will register the artisan command that will be available to you.

You can also override the default config provided by the application by
running `php artisan vendor:publish --provider "Laxity7\LaravelSwagger\SwaggerServiceProvider"` in your projects root and change the configuration in the
new `config/laravel-swagger.php` file created.

## Usage

Write code as you normally would, using the recommended practices by Laravel. This package will scan your routes and controllers and generate the documentation
for you.
Generating the swagger documentation is easy, simply run `php artisan laravel-swagger:generate` in your project root.

> Keep in mind the command will simply print out the output in your console. If you want the docs saved in a file, you can reroute the output like
> so: `php artisan laravel-swagger:generate -o=swagger.json`

If you wish to generate docs for a subset of your routes, you can pass a filter using `--filter`, for
example: `php artisan laravel-swagger:generate --filter="/api"`

By default, laravel-swagger prints out the documentation in json format, if you want it in YAML format, you can override the format using the `--format` (`-f`)
flag. Make sure to have the yaml extension installed if you choose to do so.
Format options are: `json` or `yaml`

By default, prints out the documentation to the console, if you want it in a file, you can override the output using the `--output` (`-o`) flag.

### Usage in code

You can also use the package in your code by using the `Laxity7\LaravelSwagger\Generator` class.

```php
use Laxity7\LaravelSwagger\Generator;

$generator = new Generator();
$generator->generate();
```

### Custom request generators

To add a custom request generator using the `config/laravel-swagger.php` file, you can follow these steps:

1. Open or create the `config/laravel-swagger.php` file.
2. Locate the `'requestsGenerators'` array in the configuration file. This array contains the list of request generators that will be used by
   the `Laxity7\LaravelSwagger\Generator` class.
3. Create a new class that implements the `Laxity7\LaravelSwagger\Parsers\Requests\Generators\ParameterGenerator` interface. And add this class name to
   the `'requestsGenerators'` array.
   For example, let's say you have a custom request generator class called `App\Swagger\CustomBodyParameterGenerator`. You can add it to
   the `'requestsGenerators'` array like this:

```php
'requestsGenerators' => [
    \Laxity7\LaravelSwagger\Parsers\Requests\Generators\PathParameterGenerator::class,
    \Laxity7\LaravelSwagger\Parsers\Requests\Generators\QueryParameterGenerator::class,
    \App\Swagger\CustomBodyParameterGenerator::class,
],
```

4. Save the `config/laravel-swagger.php` file and run the `php artisan laravel-swagger:generate` command.

## Example

Your route file might look like this:

```php
Route::get('/api/user/{id}', ['UserController', 'show']);
Route::get(
    '/api/user/{id}/check',
    /** 
    * Check user exist
    * @param int $id User ID 
    */ 
    fn(int $id) => User::where('id', $id)->exists();
);
```

Your sample controller might look like this:

```php
use Laxity7\LaravelSwagger\Attributes\Request;

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
    /**
    * @param int $id User ID
    */
    public function show(UserShowRequest $request, int $id)
    {
        return User::find($id);
    }
    
    /**
    * @request UserShowRequest
    */
    public function show(int $id)
    {
        $showRelationships = request()->get('show_relationships')
        return User::find($id);
    }

    #[Request(UserShowRequest::class)]
    public function show(int $id)
    {
        $showRelationships = request()->get('show_relationships')
        return User::find($id);
    }

    /**
    * @ignore
    */
    public function notShow(int $id)
    {
        return User::find($id);
    }
}
```

And the FormRequest class might look like this:
The field description is taken from the property docblock or the property annotation from the class docblock.

```php
use Illuminate\Foundation\Http\FormRequest;
/**
 * @property array $fields List of user fields
 */
class UserShowRequest extends FormRequest
{
    /** Is it necessary to show the relationship? */
    public $show_relationships;
    
    public function rules(): array
    {
        return [
            'fields' => 'array'
            'show_relationships' => 'boolean|required'
        ];
    }
}

```

Running `php artisan swagger:generate -o=swagger.json` will generate the following file:
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
                        "description": "User ID"
                    },
                    {
                        "in": "query",
                        "name": "fields",
                        "type": "array",
                        "required": false,
                        "description": "List of user fields"
                    },
                    {
                        "in": "query",
                        "name": "show_relationships",
                        "type": "boolean",
                        "required": true,
                        "description": "Is it necessary to show the relationship?"
                    }
                ]
            }
        },
        "/api/user/{id}/check": {
            "get": {
                "summary": "Check user exist",
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
                        "description": "User ID"
                    }
                ]
            }
        }
    }
}
```

## Contributing

If you wish to contribute to this project, please feel free to submit a pull request. I will review it as soon as I can.
