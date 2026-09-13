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
        $botUsername = PlatformSetting::getTelegramBotUsername();
        $botToken = PlatformSetting::getTelegramBotToken();
        $hasToken = ! empty($botToken);

        return view('admin.settings.telegram', [
            'activationSuccessMsg' => PlatformSetting::getTelegramActivationSuccessMessage(),
            'welcomeMsg' => PlatformSetting::getTelegramWelcomeMessage(),
            'defaultReplyMsg' => PlatformSetting::getTelegramDefaultReplyMessage(),
            'invalidCodeMsg' => PlatformSetting::getTelegramInvalidCodeMessage(),
            'botUsername' => $botUsername,
            'botToken' => $botToken,
            'hasToken' => $hasToken,
            'platformName' => PlatformSetting::getName(),
        ]);
    }

    /**
     * Update Telegram Bot message templates & credentials.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'telegram_bot_token' => ['nullable', 'string', 'max:255'],
            'telegram_bot_username' => ['nullable', 'string', 'max:100'],
            'telegram_msg_activation_success' => ['required', 'string', 'max:2000'],
            'telegram_msg_welcome' => ['required', 'string', 'max:2000'],
            'telegram_msg_default_reply' => ['required', 'string', 'max:2000'],
            'telegram_msg_invalid_code' => ['required', 'string', 'max:2000'],
        ], [
            'telegram_msg_activation_success.required' => 'Activation success message is required.',
            'telegram_msg_welcome.required' => 'Welcome message is required.',
            'telegram_msg_default_reply.required' => 'Default reply message is required.',
            'telegram_msg_invalid_code.required' => 'Invalid code message is required.',
        ]);

        if ($request->filled('telegram_bot_token')) {
            PlatformSetting::set('telegram_bot_token', trim($validated['telegram_bot_token']));
        }

        if ($request->filled('telegram_bot_username')) {
            PlatformSetting::set('telegram_bot_username', ltrim(trim($validated['telegram_bot_username']), '@'));
        }

        PlatformSetting::set('telegram_msg_activation_success', trim($validated['telegram_msg_activation_success']));
        PlatformSetting::set('telegram_msg_welcome', trim($validated['telegram_msg_welcome']));
        PlatformSetting::set('telegram_msg_default_reply', trim($validated['telegram_msg_default_reply']));
        PlatformSetting::set('telegram_msg_invalid_code', trim($validated['telegram_msg_invalid_code']));

        return redirect()->route('admin.settings.telegram')
            ->with('success', 'Telegram Bot settings and message templates updated successfully.');
    }

    /**
     * Send a quick test ping to verify Telegram Bot connectivity.
     */
    public function testPing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_chat_id' => ['required', 'string', 'max:50'],
        ], [
            'test_chat_id.required' => 'Telegram Chat ID is required for sending a test notification.',
        ]);

        $botToken = PlatformSetting::getTelegramBotToken();
        if (empty($botToken)) {
            return back()->with('error', 'Cannot send test ping: Telegram Bot Token is not configured.');
        }

        $platformName = PlatformSetting::getName();
        $timezone = PlatformSetting::getTimezone();
        $timeStr = now($timezone)->format('Y-m-d H:i:s') . ' ' . PlatformSetting::getTimezoneLabel();
        $text = "🔔 *[{$platformName}] Telegram Bot Test Ping*\n\nYour Telegram Bot integration is working properly! Reminders and donor notifications are operational.\n\n_Sent at {$timeStr}_";

        try {
            $endpoint = rtrim(config('rymainder.channels.telegram.endpoint', 'https://api.telegram.org'), '/') . "/bot{$botToken}/sendMessage";
            $res = \Illuminate\Support\Facades\Http::timeout(10)->post($endpoint, [
                'chat_id' => trim($validated['test_chat_id']),
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);

            if ($res->successful() && $res->json('ok') === true) {
                return back()->with('success', "Test notification successfully delivered to Telegram Chat ID {$validated['test_chat_id']}!");
            }

            $err = $res->json('description') ?? $res->body();
            return back()->with('error', "Telegram API returned error: {$err}");
        } catch (\Exception $e) {
            return back()->with('error', "Failed to connect to Telegram API: " . $e->getMessage());
        }
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
            ->with('success', 'All Telegram Bot message templates have been reset to defaults.');
    }
}
