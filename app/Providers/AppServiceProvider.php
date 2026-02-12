<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Suprimir warnings de deprecación de PHP 8.4 en Laravel 9
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
    }
}
