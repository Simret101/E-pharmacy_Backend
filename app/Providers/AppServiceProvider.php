<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\UsernameGenerator;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UsernameGenerator::class, function ($app) {
            return new UsernameGenerator($app->make(User::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

    }
}
