<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('api2')->name('api2.')->group(base_path('routes/api2.php'));
        },

    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'ministry_active' => \Modules\Ministry\Http\Middleware\IsActive::class,
            'ministry_admin' => \Modules\Ministry\Http\Middleware\IsAdmin::class,
            'institution_active' => \Modules\Institution\Http\Middleware\IsActive::class,
            'institution_admin' => \Modules\Institution\Http\Middleware\IsAdmin::class,
            'student_active' => \Modules\Student\Http\Middleware\IsActive::class,
            'student_admin' => \Modules\Student\Http\Middleware\IsAdmin::class,
            'super_admin' => \App\Http\Middleware\SuperAdmin::class,
            'apiauth' => \App\Http\Middleware\ApiAuth::class,

        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
