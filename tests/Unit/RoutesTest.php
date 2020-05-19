<?php

namespace MichaelJennings\RouteGuards\Tests\Unit;

use Illuminate\Support\Facades\Route;
use MichaelJennings\RouteGuards\Tests\Fixtures\TestGuard;

class RoutesTest extends TestCase
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

    /**
     * @test
     */
    public function itAddsMultipleGuards()
    {
        $route = Route::get('test')->guard(TestGuard::class, 'foo')->guard(TestGuard::class, 'bar');

        $this->assertEquals([
            'foo' => TestGuard::class,
            'bar' => TestGuard::class
        ], $route->action['guard']);
    }

    /**
     * @test
     */
    public function itAddsAGuardThroughTheAction()
    {
        $route = Route::get('test', ['guard' => TestGuard::class]);

        $this->assertEquals(TestGuard::class, $route->action['guard']);
    }

    /**
     * @test
     */
    public function itAddsAGuardForABindingThroughTheAction()
    {
        $route = Route::get('test', ['guard' => ['test' => TestGuard::class]]);

        $this->assertEquals(['test' => TestGuard::class], $route->action['guard']);
    }
}
