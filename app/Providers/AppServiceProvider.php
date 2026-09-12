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
        View::composer('*', function ($view) {
            $view->with([
                'platformName' => PlatformSetting::getName(),
                'platformTagline' => PlatformSetting::getTagline(),
                'platformLogo' => PlatformSetting::getLogoUrl(),
                'platformFavicon' => PlatformSetting::getFaviconUrl(),
                'hasCustomLogo' => PlatformSetting::hasCustomLogo(),
            ]);
        });
    }
}
