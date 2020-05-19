<?php

namespace MichaelJennings\RouteGuards\Tests\Unit;

use Illuminate\Support\Facades\Route;
use MichaelJennings\RouteGuards\Tests\Fixtures\TestGuard;

class RouteGuardServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function itAddsTheGuardToTheRoute()
    {
        $route = Route::get('test')->guard(TestGuard::class);

        $this->assertEquals([TestGuard::class], $route->action['guard']);
    }

    /**
     * @test
     */
    public function itAddsTheGuardWithABinding()
    {
        $route = Route::get('test')->guard(TestGuard::class, 'test');

        $this->assertEquals(['test' => TestGuard::class], $route->action['guard']);
    }
}
