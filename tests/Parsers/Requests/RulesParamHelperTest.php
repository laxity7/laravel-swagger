<?php

namespace Mtrajano\LaravelSwagger\Tests\Parsers\Requests;

use Illuminate\Routing\Route as LaravelRoute;
use Mtrajano\LaravelSwagger\DataObjects\Route;
use Mtrajano\LaravelSwagger\Parsers\Requests\RulesParamHelper;
use Mtrajano\LaravelSwagger\Tests\Stubs\Controllers\UserController;
use PHPUnit\Framework\TestCase;

final class RulesParamHelperTest extends TestCase
{
    public function testGetFormRulesFromDoc(): void
    {
        $rules = RulesParamHelper::getFormRules($this->getRoute([UserController::class, 'showFromDoc']));
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
    }

    public function testGetFormRulesFromAttribute(): void
    {
        $rules = RulesParamHelper::getFormRules($this->getRoute([UserController::class, 'showFromAttribute']));
        $this->assertIsArray($rules);
        $this->assertNotEmpty($rules);
    }

    private function getRoute(array $action): Route
    {
        return new Route(new LaravelRoute('GET', '/', $action));
    }
}
