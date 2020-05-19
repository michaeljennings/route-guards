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
        $resource = $this->getResource($route, $binding);

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
     * Get the resource to authenticate against.
     *
     * @param Route       $route
     * @param string|null $binding
     * @return object|string|null
     */
    protected function getResource(Route $route, string $binding = null)
    {
        if (! $binding) {
            $parameters = $route->parameters();

            if (count($parameters) !== 1) {
                return null;
            }

            $binding = array_keys($parameters)[0];
        }

        return $this->find($route, $binding);
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
     * Handle authorization failing.
     *
     * @throws AuthorizationException
     */
    protected function authorizationFailed(): void
    {
        throw new AuthorizationException();
    }
}
