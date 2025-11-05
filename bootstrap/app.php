<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Log all 403 errors
        $exceptions->render(function (AuthorizationException $e, $request) {
            \Log::warning('403 Authorization Exception occurred', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'user_email' => $request->user()?->email,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        });

        $exceptions->render(function (AccessDeniedHttpException $e, $request) {
            \Log::warning('403 Access Denied HTTP Exception occurred', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'user_email' => $request->user()?->email,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        });

        $exceptions->render(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 403) {
                \Log::warning('403 HTTP Exception occurred', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_id' => $request->user()?->id,
                    'user_email' => $request->user()?->email,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        });
    })
    ->create();
