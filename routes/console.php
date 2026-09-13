<?php

use App\Models\PlatformSetting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Automated Sponsor Reminder Daily Schedule
|--------------------------------------------------------------------------
|
| Evaluates all active sponsors daily at the configured dispatch time (e.g. 07:00 WIB),
| matches active waves, and dispatches queue jobs for Email, WhatsApp, and Telegram.
|
*/
$dispatchTime = PlatformSetting::get('reminder_dispatch_time', env('REMINDER_DISPATCH_TIME', '07:00'));
$timezone = PlatformSetting::getTimezone();

Schedule::command('reminders:send')
    ->dailyAt($dispatchTime)
    ->timezone($timezone)
    ->withoutOverlapping()
    ->runInBackground();

/*
|--------------------------------------------------------------------------
| Custom Scheduled Reminders
|--------------------------------------------------------------------------
|
| Periodically checks and triggers active custom reminders (hourly, multi-daily, etc.)
| based on their individual next_run_at calculation.
|
*/
Schedule::command('custom-reminders:run')
    ->everyMinute()
    ->timezone($timezone)
    ->withoutOverlapping()
    ->runInBackground();
