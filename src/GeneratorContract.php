<?php

namespace Laxity7\LaravelSwagger;

interface GeneratorContract
{
    public function generate(): array;

    public function setRouteFilter(string $routeFilter): self;
}
