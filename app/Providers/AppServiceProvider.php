<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    // public function register(): void
    // {
    //     //
    // }

//     public function register(): void
// {
//     // We manually register the Tenancy providers here to avoid the [files] error
//     if (class_exists(\Stancl\Tenancy\TenancyServiceProvider::class)) {
//         $this->app->register(\Stancl\Tenancy\TenancyServiceProvider::class);
//         $this->app->register(\App\Providers\TenancyServiceProvider::class);
//     }
// }
// public function register(): void
// {
//     // Now that auto-discovery is off, we control the timing.
//     // We only register these once the core app is ready.
//     if ($this->app->bound('files')) {
//         $this->app->register(\Stancl\Tenancy\TenancyServiceProvider::class);
//         $this->app->register(\App\Providers\TenancyServiceProvider::class);
//     }
// }

public function register(): void
{
    // Register the package providers manually after the app is stable
    // if (class_exists(\Stancl\Tenancy\TenancyServiceProvider::class)) {
    //     $this->app->register(\Stancl\Tenancy\TenancyServiceProvider::class);
    //     $this->app->register(\App\Providers\TenancyServiceProvider::class);
    // }
}

    /**
     * Bootstrap any application services.
     */
    // public function boot(): void
    // {
    //     //
    // }

    public function boot(): void
{
    // We only load Tenancy once the core (including 'files') is fully ready
    if (class_exists(\Stancl\Tenancy\TenancyServiceProvider::class)) {
        $this->app->register(\Stancl\Tenancy\TenancyServiceProvider::class);
        $this->app->register(\App\Providers\TenancyServiceProvider::class);
    }
}
}
