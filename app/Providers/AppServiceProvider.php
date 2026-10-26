<?php

namespace App\Providers;

use App\Hashing\BetterAuthHasher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Hash::extend('better-auth', function () {
            return new BetterAuthHasher();
        });
    }
}
