<?php

namespace TamkeenTech\Payfort\Providers;

use Illuminate\Support\ServiceProvider;

class PayfortServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/payfort.php', 'payfort');

        $this->publishes([
            __DIR__.'/../../config/payfort.php' => config_path('payfort.php'),
        ], 'payfort-config');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
