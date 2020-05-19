<?php

namespace MichaelJennings\RouteGuards\Tests\Unit;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Routing\Route;
use MichaelJennings\RouteGuards\RouteGuard;
use Mockery;

class RouteGuardTest extends TestCase
{
    /**
     * @test
     */
    public function itPassesAuthorization()
    {
        $route = Mockery::mock(Route::class);

        $route->shouldReceive('getAction')->andReturn('TestController@index');
        $route->shouldReceive('parameters')->andReturn([]);

        $guard = new class extends RouteGuard {
            public function authorize(Route $route): bool
            {
                return true;
            }
        };

        $guard->guard($route);
    }

    /**
     * @test
     */
    public function itThrowsAnAuthorizationException()
    {
        $this->expectException(AuthorizationException::class);

        $route = Mockery::mock(Route::class);

        $route->shouldReceive('getAction')->andReturn('TestController@index');
        $route->shouldReceive('parameters')->andReturn([]);

        $guard = new class extends RouteGuard {
            public function authorize(Route $route): bool
            {
                return false;
            }
        };

        $guard->guard($route);
    }

    /**
     * @test
     */
    public function itAuthorizesUsingTheControllerMethodName()
    {
        $route = Mockery::mock(Route::class);

        $route->shouldReceive('getAction')->andReturn('TestController@index');
        $route->shouldReceive('parameters')->andReturn([]);

        $guard = new class extends RouteGuard {
            public function authorize(Route $route): bool
            {
                return false;
            }

            public function index(Route $route): bool
            {
                return true;
            }
        };

        $guard->guard($route);
    }

    /**
     * @test
     */
    public function itUsesTheDefaultNameIfThereIsNotAUses()
    {
        $route = Mockery::mock(Route::class);

        $route->shouldReceive('getAction')->andReturn(null);
        $route->shouldReceive('parameters')->andReturn([]);

        $guard = new class extends RouteGuard {
            public function authorize(Route $route): bool
            {
                return true;
            }
        };

        $guard->guard($route);
    }

    /**
     * @test
     */
    public function itThrowsACustomAuthorizationException()
    {
        $this->expectException(Exception::class);

        $route = Mockery::mock(Route::class);

        $route->shouldReceive('getAction')->andReturn('TestController@index');
        $route->shouldReceive('parameters')->andReturn([]);

        $guard = new class extends RouteGuard {
            public function index(Route $route): bool
            {
                return false;
            }

            public function indexFailed(): void
            {
                throw new Exception('Testing custom exceptions');
            }
        };

        $guard->guard($route);
    }

    /**
     * @test
     */
    public function itAuthorisesARouteBinding()
    {
        $route = Mockery::mock(Route::class);

        $route->shouldReceive('getAction')->andReturn('TestController@index');
        $route->shouldReceive('parameter')->andReturn('foo');

        $guard = new class extends RouteGuard {
            public function authorize(Route $route, string $parameter): bool
            {
                return $parameter === 'foo';
            }
        };

        $guard->guard($route, 'test');
    }

    /**
     * @test
     */
    public function itBindsToTheFirstParameterIfABindingIsNotSpecified()
    {
        $route = Mockery::mock(Route::class);

        $route->shouldReceive('getAction')->andReturn('TestController@index');
        $route->shouldReceive('parameters')->andReturn(['foo' => 'bar']);
        $route->shouldReceive('parameter')->andReturn('bar');

        $guard = new class extends RouteGuard {
            public function authorize(Route $route, string $parameter): bool
            {
                return $parameter === 'bar';
            }
        };

        $guard->guard($route);
    }

    /**
     * @test
     */
    public function itDoesNotBindIfThereIsMoreThanOneParameter()
    {
        $route = Mockery::mock(Route::class);

        $route->shouldReceive('getAction')->andReturn('TestController@index');
        $route->shouldReceive('parameters')->andReturn(['foo' => 'bar', 'baz' => 'qux']);

        $guard = new class extends RouteGuard {
            public function authorize(Route $route, $parameter): bool
            {
                return is_null($parameter);
            }
        };

        $guard->guard($route);
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
