<?php

namespace Mtrajano\LaravelSwagger\Attributes;

use Attribute;
use Illuminate\Http\Request as LaravelRequest;
use InvalidArgumentException;

#[Attribute]
final class Request
{
    /**
     * @param  LaravelRequest|class-string<LaravelRequest>  $request
     */
    public function __construct(public readonly LaravelRequest|string $request)
    {
        if (is_string($this->request) && !is_subclass_of($this->request, LaravelRequest::class)) {
            throw new InvalidArgumentException('Request must be a subclass of \Illuminate\Http\Request');
        }
    }
}
