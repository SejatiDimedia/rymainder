<?php

namespace App\Providers;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $timezone = PlatformSetting::getTimezone();
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        } catch (\Throwable $e) {
            // Graceful fallback during setup or early migrations
        }

        View::composer('*', function ($view) {
            $view->with([
                'platformName' => PlatformSetting::getName(),
                'platformTagline' => PlatformSetting::getTagline(),
                'platformLogo' => PlatformSetting::getLogoUrl(),
                'platformFavicon' => PlatformSetting::getFaviconUrl(),
                'hasCustomLogo' => PlatformSetting::hasCustomLogo(),
                'platformTimezone' => PlatformSetting::getTimezone(),
                'platformTimezoneLabel' => PlatformSetting::getTimezoneLabel(),
            ]);
        });
    }
}
