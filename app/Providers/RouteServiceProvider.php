<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();

        Route::fallback(function () {
            return response()->json(['error' => 'Route not found'], 404);
        });
    }
}
