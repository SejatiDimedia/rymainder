<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Telegram Bot Webhook Route (Protected by secret verification and exempt from CSRF)
Route::post('/webhooks/telegram', \App\Http\Controllers\Webhooks\TelegramWebhookController::class)
    ->middleware('telegram.secret')
    ->name('webhooks.telegram');


Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard Overview
    Route::get('/dashboard', \App\Http\Controllers\Admin\DashboardController::class)->name('dashboard');

    // Sponsor Management
    Route::resource('sponsors', \App\Http\Controllers\Admin\SponsorController::class)->names([
        'index' => 'admin.sponsors.index',
        'create' => 'admin.sponsors.create',
        'store' => 'admin.sponsors.store',
        'show' => 'admin.sponsors.show',
        'edit' => 'admin.sponsors.edit',
        'update' => 'admin.sponsors.update',
        'destroy' => 'admin.sponsors.destroy',
    ]);
    Route::post('/sponsors/{sponsor}/send-reminder', [\App\Http\Controllers\Admin\ManualReminderController::class, 'send'])->name('admin.sponsors.send-reminder');
    Route::post('/sponsors/{sponsor}/send-telegram-invitation', [\App\Http\Controllers\Admin\SponsorController::class, 'sendTelegramInvitation'])->name('admin.sponsors.send-telegram-invitation');
    Route::post('/sponsors/{sponsor}/check-telegram-status', [\App\Http\Controllers\Admin\SponsorController::class, 'checkTelegramStatus'])->name('admin.sponsors.check-telegram-status');
    Route::delete('/sponsors/{sponsor}/disconnect-telegram', [\App\Http\Controllers\Admin\SponsorController::class, 'disconnectTelegram'])->name('admin.sponsors.disconnect-telegram');

    // Platform & Reminder Settings (Restricted to Super Admin)
    Route::middleware('role:super_admin')->group(function () {
        // Platform & Branding
        Route::get('/settings/platform', [\App\Http\Controllers\Admin\PlatformSettingController::class, 'index'])->name('admin.settings.platform');
        Route::put('/settings/platform', [\App\Http\Controllers\Admin\PlatformSettingController::class, 'update'])->name('admin.settings.platform.update');
        Route::delete('/settings/platform/logo', [\App\Http\Controllers\Admin\PlatformSettingController::class, 'resetLogo'])->name('admin.settings.platform.reset-logo');
        Route::delete('/settings/platform/favicon', [\App\Http\Controllers\Admin\PlatformSettingController::class, 'resetFavicon'])->name('admin.settings.platform.reset-favicon');

        // Reminder Waves & Dispatch Engine
        Route::get('/settings/reminders', [\App\Http\Controllers\Admin\ReminderSettingController::class, 'index'])->name('admin.settings.index');
        Route::put('/settings/reminders/schedule-time', [\App\Http\Controllers\Admin\ReminderSettingController::class, 'updateScheduleTime'])->name('admin.settings.schedule-time');
        Route::post('/settings/reminders', [\App\Http\Controllers\Admin\ReminderSettingController::class, 'store'])->name('admin.settings.store');
        Route::put('/settings/reminders/{setting}', [\App\Http\Controllers\Admin\ReminderSettingController::class, 'update'])->name('admin.settings.update');
        Route::delete('/settings/reminders/{setting}', [\App\Http\Controllers\Admin\ReminderSettingController::class, 'destroy'])->name('admin.settings.destroy');

        // Standalone Custom Reminders
        Route::resource('custom-reminders', \App\Http\Controllers\Admin\CustomReminderController::class)->names([
            'index' => 'admin.custom-reminders.index',
            'create' => 'admin.custom-reminders.create',
            'store' => 'admin.custom-reminders.store',
            'edit' => 'admin.custom-reminders.edit',
            'update' => 'admin.custom-reminders.update',
            'destroy' => 'admin.custom-reminders.destroy',
        ]);
        Route::patch('/custom-reminders/{customReminder}/toggle', [\App\Http\Controllers\Admin\CustomReminderController::class, 'toggle'])->name('admin.custom-reminders.toggle');
        Route::post('/custom-reminders/{customReminder}/run-now', [\App\Http\Controllers\Admin\CustomReminderController::class, 'runNow'])->name('admin.custom-reminders.run-now');
    });

    // Global Reminder Audit Logs
    Route::get('/logs', [\App\Http\Controllers\Admin\ReminderLogController::class, 'index'])->name('admin.logs.index');
    Route::post('/logs/{log}/retry', [\App\Http\Controllers\Admin\ReminderLogController::class, 'retry'])->name('admin.logs.retry');

    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
