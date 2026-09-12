<?php

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
| Evaluates all active sponsors daily at 08:00 WIB, matches active waves,
| and dispatches queue jobs for Email, WhatsApp, and Telegram reminders.
|
*/
try {
    if (\Illuminate\Support\Facades\Schema::hasTable('reminder_settings')) {
        $waves = \App\Domain\Reminder\Models\ReminderSetting::where('is_active', true)->get();

        if ($waves->isNotEmpty()) {
            foreach ($waves as $wave) {
                $time = $wave->dispatch_time ?: '07:00';
                $event = Schedule::command("reminders:send --wave={$wave->id}")
                    ->timezone(config('app.timezone', 'Asia/Jakarta'))
                    ->withoutOverlapping()
                    ->runInBackground();

                if ($wave->schedule_frequency === 'weekly' && ! empty($wave->schedule_day)) {
                    $event->weeklyOn((int) $wave->schedule_day, $time);
                } else {
                    $event->dailyAt($time);
                }
            }
        } else {
            $fallbackTime = env('REMINDER_DISPATCH_TIME', '07:00');
            Schedule::command('reminders:send')
                ->dailyAt($fallbackTime)
                ->timezone(config('app.timezone', 'Asia/Jakarta'))
                ->withoutOverlapping()
                ->runInBackground();
        }
    }
} catch (\Throwable $e) {
    $fallbackTime = env('REMINDER_DISPATCH_TIME', '07:00');
    Schedule::command('reminders:send')
        ->dailyAt($fallbackTime)
        ->timezone(config('app.timezone', 'Asia/Jakarta'))
        ->withoutOverlapping()
        ->runInBackground();
}
