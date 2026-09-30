<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatically synchronize active ZKTeco biometric devices every 5 minutes
Schedule::command('zkteco:sync')->everyFiveMinutes()->withoutOverlapping();
