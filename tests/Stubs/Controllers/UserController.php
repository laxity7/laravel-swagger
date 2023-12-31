<?php

namespace Laxity7\LaravelSwagger\Tests\Stubs\Controllers;

use Illuminate\Routing\Controller;
use Laxity7\LaravelSwagger\Attributes\Request;
use Laxity7\LaravelSwagger\Tests\Stubs\Requests\UserShowRequest;
use Laxity7\LaravelSwagger\Tests\Stubs\Requests\UserStoreRequest;

final class UserController extends Controller
{
    /** Get a list of of users in the application */
    public function index(): string
    {
        return json_encode([['first_name' => 'John'], ['first_name' => 'Jack']]);
    }

    /**
     * @param  int  $id  User id
     * @param  UserShowRequest  $request
     * @param $uuid
     * @return string
     */
    public function show(int $id, UserShowRequest $request, $uuid): string
    {
        return json_encode(['first_name' => 'John']);
    }

    /**
     * @request  UserShowRequest
     */
    public function showFromDoc(): string
    {
        return json_encode(['first_name' => 'John']);
    }

    #[Request(UserShowRequest::class)]
    public function showFromAttribute(): string
    {
        return json_encode(['first_name' => 'John']);
    }

    /**
     * Store a new user in the application
     *
     * Data is validated [see description here](https://example.com) so no bad data can be passed.
     * Please read the documentation for more information
     *
     * @param  UserStoreRequest  $request
     * @return string
     * @deprecated
     */
    public function store(UserStoreRequest $request): string
    {
        return json_encode($request->all());
    }

    /**
     * @deprecated
     */
    public function details(): string
    {
        return json_encode([]);
    }
}
