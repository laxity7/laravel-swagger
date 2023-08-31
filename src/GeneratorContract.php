<?php

namespace Mtrajano\LaravelSwagger;

interface GeneratorContract
{
    public function generate(): array;

    public function setRouteFilter(string $routeFilter): self;
}
