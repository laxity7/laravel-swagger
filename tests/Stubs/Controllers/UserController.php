<?php

namespace Mtrajano\LaravelSwagger\Tests\Stubs\Controllers;

use Illuminate\Routing\Controller;
use Mtrajano\LaravelSwagger\Tests\Stubs\Requests\UserShowRequest;
use Mtrajano\LaravelSwagger\Tests\Stubs\Requests\UserStoreRequest;

final class UserController extends Controller
{
    /** Get a list of of users in the application */
    public function index(): string
    {
        return json_encode([['first_name' => 'John'], ['first_name' => 'Jack']]);
    }

    public function show(UserShowRequest $request, int $id): string
    {
        return json_encode(['first_name' => 'John']);
    }

    /**
     * Store a new user in the application
     *
     * Data is validated [see description here](https://example.com) so no bad data can be passed.
     * Please read the documentation for more information
     *
     * @param UserStoreRequest $request
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
