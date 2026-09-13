<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TelegramSettingController extends Controller
{
    /**
     * Display Telegram Bot message templates settings.
     */
    public function index(): View
    {
        $botUsername = config('rymainder.channels.telegram.bot_username');
        $hasToken = ! empty(config('rymainder.channels.telegram.bot_token'));

        return view('admin.settings.telegram', [
            'activationSuccessMsg' => PlatformSetting::getTelegramActivationSuccessMessage(),
            'welcomeMsg' => PlatformSetting::getTelegramWelcomeMessage(),
            'defaultReplyMsg' => PlatformSetting::getTelegramDefaultReplyMessage(),
            'invalidCodeMsg' => PlatformSetting::getTelegramInvalidCodeMessage(),
            'botUsername' => $botUsername,
            'hasToken' => $hasToken,
            'platformName' => PlatformSetting::getName(),
        ]);
    }

    /**
     * Update Telegram Bot message templates.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'telegram_msg_activation_success' => ['required', 'string', 'max:2000'],
            'telegram_msg_welcome' => ['required', 'string', 'max:2000'],
            'telegram_msg_default_reply' => ['required', 'string', 'max:2000'],
            'telegram_msg_invalid_code' => ['required', 'string', 'max:2000'],
        ], [
            'telegram_msg_activation_success.required' => 'Pesan aktivasi berhasil wajib diisi.',
            'telegram_msg_welcome.required' => 'Pesan sambutan bot wajib diisi.',
            'telegram_msg_default_reply.required' => 'Pesan balasan default wajib diisi.',
            'telegram_msg_invalid_code.required' => 'Pesan kode aktivasi invalid wajib diisi.',
        ]);

        PlatformSetting::set('telegram_msg_activation_success', trim($validated['telegram_msg_activation_success']));
        PlatformSetting::set('telegram_msg_welcome', trim($validated['telegram_msg_welcome']));
        PlatformSetting::set('telegram_msg_default_reply', trim($validated['telegram_msg_default_reply']));
        PlatformSetting::set('telegram_msg_invalid_code', trim($validated['telegram_msg_invalid_code']));

        return redirect()->route('admin.settings.telegram')
            ->with('success', 'Template pesan balasan Bot Telegram berhasil diperbarui.');
    }

    /**
     * Reset Telegram Bot message templates back to defaults.
     */
    public function resetDefaults(): RedirectResponse
    {
        foreach (array_keys(PlatformSetting::defaultTelegramMessages()) as $key) {
            PlatformSetting::remove($key);
        }

        return redirect()->route('admin.settings.telegram')
            ->with('success', 'Seluruh template pesan Bot Telegram telah dikembalikan ke format default.');
    }
}
