<?php

namespace Mtrajano\LaravelSwagger\Tests\Stubs\Controllers;

use Illuminate\Routing\Controller;

final class ApiController extends Controller
{
    public function index(): string
    {
        return json_encode(['result' => 'success']);
    }

    public function store(): string
    {
        return json_encode(['result' => 'success']);
    }
}
