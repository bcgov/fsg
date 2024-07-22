<?php

use App\Jobs\MidnightJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
    \Log::info("Inspire Finished");
})->purpose('Display an inspiring quote')->everyMinute();

Schedule::job(new MidnightJob)->timezone('America/Vancouver')->daily();
