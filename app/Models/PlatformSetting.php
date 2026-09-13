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
            'Asia/Jakarta' => 'WIB — Western Indonesia Time (UTC+7, Sumatra/Java/West Kalimantan)',
            'Asia/Makassar' => 'WITA — Central Indonesia Time (UTC+8, Bali/Sulawesi/East Kalimantan/NTB/NTT)',
            'Asia/Jayapura' => 'WIT — Eastern Indonesia Time (UTC+9, Maluku/Papua)',
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

    /**
     * Default Telegram Bot message templates.
     */
    public static function defaultTelegramMessages(): array
    {
        return [
            'telegram_msg_activation_success' => "Assalamu'alaikum Wr. Wb. / Salam Sejahtera,\n\nAlhamdulillah, terima kasih Bpk/Ibu *{sponsor_name}*!\n\nAkun Telegram Anda telah berhasil terhubung dengan sistem pengingat donasi *{platform_name}*. Mulai saat ini, jadwal pengingat komitmen donasi rutin Anda akan otomatis dikirimkan ke chat ini.\n\nSemoga Allah SWT membalas segala amal kebaikan Bpk/Ibu dengan keberkahan yang berlipat ganda. Aamiin.",
            'telegram_msg_welcome' => "Selamat datang di Bot Resmi {platform_name}.\n\nUntuk menghubungkan akun sponsor/donatur Anda, silakan klik tautan aktivasi khusus yang telah dibagikan oleh staf yayasan, atau hubungi admin kami.",
            'telegram_msg_default_reply' => "Terima kasih telah menghubungi kami. Pesan Anda telah kami terima. Untuk informasi lebih lanjut mengenai donasi, silakan hubungi staf yayasan di nomor WhatsApp resmi.",
            'telegram_msg_invalid_code' => "Kode aktivasi tidak dikenali atau sudah kedaluwarsa. Mohon hubungi admin yayasan untuk mendapatkan link aktivasi yang baru.",
        ];
    }

    public static function getTelegramActivationSuccessMessage(): string
    {
        return (string) (static::get('telegram_msg_activation_success') ?: static::defaultTelegramMessages()['telegram_msg_activation_success']);
    }

    public static function getTelegramWelcomeMessage(): string
    {
        return (string) (static::get('telegram_msg_welcome') ?: static::defaultTelegramMessages()['telegram_msg_welcome']);
    }

    public static function getTelegramDefaultReplyMessage(): string
    {
        return (string) (static::get('telegram_msg_default_reply') ?: static::defaultTelegramMessages()['telegram_msg_default_reply']);
    }

    public static function getTelegramInvalidCodeMessage(): string
    {
        return (string) (static::get('telegram_msg_invalid_code') ?: static::defaultTelegramMessages()['telegram_msg_invalid_code']);
    }

    /**
     * Get the configured Telegram Bot Token (DB platform setting or .env config fallback).
     */
    public static function getTelegramBotToken(): ?string
    {
        return static::get('telegram_bot_token') ?: config('rymainder.channels.telegram.bot_token');
    }

    /**
     * Get the configured Telegram Bot Username (DB platform setting or .env config fallback).
     */
    public static function getTelegramBotUsername(): ?string
    {
        $username = static::get('telegram_bot_username') ?: config('rymainder.channels.telegram.bot_username');
        return $username ? ltrim($username, '@') : null;
    }
}

