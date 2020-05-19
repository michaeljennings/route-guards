<?php

namespace MichaelJennings\RouteGuards;

use Closure;
use Illuminate\Routing\Route;

class GuardRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($guard = $request->route()->getAction('guard')) {
            if (is_array($guard)) {
                foreach ($guard as $binding => $class) {
                    $this->guard($request->route(), $class, is_string($binding) ? $binding : null);
                }
            } else {
                $this->guard($request->route(), $guard);
            }
        }

        return $next($request);
    }

    /**
     * Run the route guard against the route.
     *
     * @param Route       $route
     * @param string      $class
     * @param string|null $binding
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function guard(Route $route, string $class, string $binding = null): void
    {
        /** @var RouteGuard $guard */
        $guard = app($class);

        $guard->guard($route, $binding);
    }
}
