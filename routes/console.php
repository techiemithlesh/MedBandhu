<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Re-derive subscription access flags daily (requires the scheduler / cron:
// * * * * * php artisan schedule:run).
Schedule::command('hms:refresh-subscriptions')->dailyAt('01:00');

// Rebuild the public demo hospital from clean sample data every night.
Schedule::command('hms:reset-demo')->dailyAt('03:00');
