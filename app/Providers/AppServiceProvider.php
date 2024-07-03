<?php

namespace App\Providers;

use App\Events\ClaimSubmitted1;
use App\Events\StaffRoleChanged1;
use App\Listeners\ProcessSubmittedClaim1;
use App\Listeners\SendActiveRoleNotification1;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (config('app.env') === 'production' || config('app.env') === 'development') {
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
