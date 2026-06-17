<?php

use App\Providers\AppServiceProvider;

// return [
//     App\Providers\AppServiceProvider::class,
//     // App\Providers\TenancyServiceProvider::class,
// ];

return [
    App\Providers\AppServiceProvider::class,
    // Add it here manually so we control the order
    // Stancl\Tenancy\TenancyServiceProvider::class, 
    // App\Providers\TenancyServiceProvider::class,
];