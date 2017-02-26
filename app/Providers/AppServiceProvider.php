<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('App\Services\Contracts\SimulatorServiceInterface', 'App\Services\SimulatorService');
        $this->app->singleton('App\Services\Contracts\ShopServiceInterface', 'App\Services\ShopService');
        $this->app->singleton('App\Services\Contracts\RobotServiceInterface', 'App\Services\RobotService');

        $this->app->singleton('App\Repositories\Contracts\ShopRepositoryInterface', 'App\Repositories\ShopRepository');
    }
}
