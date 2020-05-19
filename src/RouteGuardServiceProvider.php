<?php

namespace MichaelJennings\RouteGuards;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class RouteGuardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Route::macro('guard', function (string $guard, string $binding = null) {
            $this->action['guard'] = $binding ? [$binding => $guard] : $guard;
        });
    }
}
