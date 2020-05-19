<?php

namespace MichaelJennings\RouteGuards\Tests\Unit;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use MichaelJennings\RouteGuards\GuardRoutes;
use MichaelJennings\RouteGuards\Tests\Fixtures\TestGuard;
use Mockery;

class GuardRoutesTest extends TestCase
{
    /**
     * @var GuardRoutes
     */
    protected $middleware;

    public function loadFixtures()
    {
        $this->middleware = app(GuardRoutes::class);
    }

    /**
     * @test
     */
    public function itPassesUsingAGuard()
    {
        $request = $this->mockRequest(TestGuard::class, 'TestController@passes');

        $this->middleware->handle($request, function() {});
    }

    /**
     * @test
     */
    public function itFailsUsingAGuard()
    {
        $this->expectException(AuthorizationException::class);

        $request = $this->mockRequest(TestGuard::class, 'TestController@fails');

        $this->middleware->handle($request, function() {});
    }

    /**
     * @test
     */
    public function itPassesUsingAnArrayOfGuards()
    {
        $request = $this->mockRequest(['test' => TestGuard::class], 'TestController@passes');

        $this->middleware->handle($request, function() {});
    }

    /**
     * @test
     */
    public function itFailsUsingAClassName()
    {
        $this->expectException(AuthorizationException::class);

        $request = $this->mockRequest(['test' => TestGuard::class], 'TestController@fails');

        $this->middleware->handle($request, function() {});
    }

    protected function mockRequest($guards, string $method = 'authorize')
    {
        $route = Mockery::mock(Route::class);

        $route->shouldReceive('parameters')->andReturn([]);
        $route->shouldReceive('getAction')->andReturnUsing(function ($argument) use ($method, $guards) {
            if ($argument === 'guard') {
                return $guards;
            }

            return $method;
        });

        if (is_array($guards)) {
            $route->shouldReceive('parameter')->andReturn('foo');
        }

        $request = Mockery::mock(Request::class);

        $request->shouldReceive('route')->andReturn($route);

        return $request;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }
}
