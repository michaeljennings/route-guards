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
            if (! isset($this->action['guard'])) {
                $this->action['guard'] = [];
            }

            if ($binding) {
                $this->action['guard'][$binding] = $guard;
            } else {
                $this->action['guard'][] = $guard;
            }

            return $this;
        });
    }
}
