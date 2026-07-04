<?php

use App\Console\Commands\GenerateRecurringBills;
use App\Console\Commands\RemindBillItems;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(GenerateRecurringBills::class)->dailyAt('02:00');
Schedule::command(RemindBillItems::class)->dailyAt('07:00');
