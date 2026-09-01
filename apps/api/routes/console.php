<?php

use App\Jobs\RecoverStuckOrdersJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(RecoverStuckOrdersJob::class)->everyMinute();
Schedule::command('commerce:sync-test-catalog')->everyMinute();
