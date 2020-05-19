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
     * @param string|null $method
     * @throws AuthorizationException
     */
    public function guard(Route $route, string $method = null): void
    {
        if (! $method) {
            $method = $this->getMethod($route);
        }

        if (! $this->$method($route)) {
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
     * Handle authorization failing.
     *
     * @throws AuthorizationException
     */
    protected function authorizationFailed(): void
    {
        throw new AuthorizationException();
    }
}
