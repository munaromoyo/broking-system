<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            foreach (config('tenancy.central_domains') as $domain) {
                Route::middleware('web')
                    ->domain($domain)
                    ->group(base_path('routes/web.php'));
            }

            Route::middleware([
                'web',
                // \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            ])->group(base_path('routes/tenant.php'));
        },
    )

    ->withMiddleware(function (Middleware $middleware) {

            $middleware->alias([
                'tenant.admin' => \App\Http\Middleware\EnsureUserIsAdmin::class, // Fixed backslash
            ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();