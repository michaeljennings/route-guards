<?php

namespace MichaelJennings\RouteGuards;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Routing\Route;

class RouteGuard
{
    /**
     * Get the default authorization method to be called.
     *
     * @var string
     */
    protected $defaultMethod = 'authorize';

    /**
     * Authorize that the user can access the route.
     *
     * @param Route       $route
     * @param string|null $binding
     * @throws AuthorizationException
     */
    public function guard(Route $route, string $binding = null): void
    {
        $method = $this->getMethod($route);
        $resource = $binding ? $this->find($route, $binding) : null;

        if (! $this->$method($route, $resource)) {
            $failureMethod = $method . 'Failed';

            if (method_exists($this, $failureMethod)) {
                $this->$failureMethod();
            } else {
                $this->authorizationFailed();
            }
        }
    }

    /**
     * Find the resource to authenticate from its route binding.
     *
     * @param Route  $route
     * @param string $binding
     * @return object|string|null
     */
    protected function find(Route $route, string $binding)
    {
        return $route->parameter($binding);
    }

    /**
     * Get the method to be called on the guard.
     *
     * @param Route $route
     * @return string
     */
    protected function getMethod(Route $route): string
    {
        $uses = $route->getAction('uses');

        if (! $uses) {
            return $this->defaultMethod;
        }

        $method = last(explode('@', $uses));

        return method_exists($this, $method) ? $method : $this->defaultMethod;
    }

    /**
     * Handle authorization failing.
     *
     * @throws AuthorizationException
     */
    protected function authorizationFailed(): void
    {
        throw new AuthorizationException();
    }
}
