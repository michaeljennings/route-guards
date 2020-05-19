<?php

namespace MichaelJennings\RouteGuards\Tests\Fixtures;

use Illuminate\Routing\Route;
use MichaelJennings\RouteGuards\RouteGuard;

class TestGuard extends RouteGuard
{
    public function passes(Route $route): bool
    {
        return true;
    }

    public function fails(Route $route): bool
    {
        return false;
    }
}
