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

    // Platform & Reminder Settings (Restricted to Super Admin)
    Route::middleware('role:super_admin')->group(function () {
        // Platform & Branding
        Route::get('/settings/platform', [\App\Http\Controllers\Admin\PlatformSettingController::class, 'index'])->name('admin.settings.platform');
        Route::put('/settings/platform', [\App\Http\Controllers\Admin\PlatformSettingController::class, 'update'])->name('admin.settings.platform.update');
        Route::delete('/settings/platform/logo', [\App\Http\Controllers\Admin\PlatformSettingController::class, 'resetLogo'])->name('admin.settings.platform.reset-logo');
        Route::delete('/settings/platform/favicon', [\App\Http\Controllers\Admin\PlatformSettingController::class, 'resetFavicon'])->name('admin.settings.platform.reset-favicon');

        // Reminder Waves
        Route::get('/settings/reminders', [\App\Http\Controllers\Admin\ReminderSettingController::class, 'index'])->name('admin.settings.index');
        Route::put('/settings/reminders/{setting}', [\App\Http\Controllers\Admin\ReminderSettingController::class, 'update'])->name('admin.settings.update');
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
