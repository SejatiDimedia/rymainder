<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Retrieve a setting value by key with cache.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::rememberForever("platform_setting_{$key}", function () use ($key, $default) {
                $setting = static::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Update or create a setting value.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("platform_setting_{$key}");
    }

    /**
     * Delete a setting key.
     */
    public static function remove(string $key): void
    {
        static::where('key', $key)->delete();
        Cache::forget("platform_setting_{$key}");
    }

    /**
     * Get the configured platform name.
     */
    public static function getName(): string
    {
        return (string) (static::get('platform_name') ?: config('app.name', 'Rymainder'));
    }

    /**
     * Get the configured platform tagline.
     */
    public static function getTagline(): string
    {
        return (string) (static::get('platform_tagline') ?: 'Pledge Cloud');
    }

    /**
     * Get the configured platform timezone (e.g. Asia/Jakarta, Asia/Makassar, Asia/Jayapura, UTC).
     */
    public static function getTimezone(): string
    {
        return (string) (static::get('platform_timezone') ?: config('app.timezone', env('APP_TIMEZONE', 'Asia/Jakarta')));
    }

    /**
     * Get short display label for timezone (WIB, WITA, WIT, UTC).
     */
    public static function getTimezoneLabel(): string
    {
        $tz = static::getTimezone();

        return match ($tz) {
            'Asia/Jakarta', 'Asia/Pontianak' => 'WIB',
            'Asia/Makassar', 'Asia/Ujung_Pandang' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            'UTC' => 'UTC',
            default => $tz,
        };
    }

    /**
     * Supported Indonesian and global timezones.
     */
    public static function supportedTimezones(): array
    {
        return [
            'Asia/Jakarta' => 'WIB — Waktu Indonesia Barat (UTC+7, Sumatra/Jawa/Kalbar)',
            'Asia/Makassar' => 'WITA — Waktu Indonesia Tengah (UTC+8, Bali/Sulawesi/NTB/NTT/Kalsel/Kaltim)',
            'Asia/Jayapura' => 'WIT — Waktu Indonesia Timur (UTC+9, Maluku/Papua)',
            'UTC' => 'UTC — Coordinated Universal Time',
        ];
    }

    /**
     * Get the configured logo URL or fallback to default.
     */
    public static function getLogoUrl(): string
    {
        $customLogo = static::get('platform_logo');

        if ($customLogo && Storage::disk('public')->exists($customLogo)) {
            return Storage::url($customLogo);
        }

        return asset('images/logo.png');
    }

    /**
     * Check if a custom logo is currently uploaded.
     */
    public static function hasCustomLogo(): bool
    {
        $customLogo = static::get('platform_logo');
        return $customLogo && Storage::disk('public')->exists($customLogo);
    }

    /**
     * Get the configured favicon URL or fallback to default.
     */
    public static function getFaviconUrl(): string
    {
        $customFavicon = static::get('platform_favicon');

        if ($customFavicon && Storage::disk('public')->exists($customFavicon)) {
            return Storage::url($customFavicon);
        }

        // Fallback to custom logo if available
        $customLogo = static::get('platform_logo');
        if ($customLogo && Storage::disk('public')->exists($customLogo)) {
            return Storage::url($customLogo);
        }

        return asset('favicon.png');
    }
}
