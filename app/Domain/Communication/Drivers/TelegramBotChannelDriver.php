<?php

namespace App\Domain\Communication\Drivers;

use App\Domain\Communication\Contracts\ReminderChannelDriverInterface;
use App\Domain\Communication\DataTransferObjects\DeliveryResult;
use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Sponsor\Models\Sponsor;
use App\Models\PlatformSetting;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotChannelDriver implements ReminderChannelDriverInterface
{
    public function channel(): ReminderChannel
    {
        return ReminderChannel::TELEGRAM;
    }

    public function send(Sponsor $sponsor, NotificationPayload $payload): DeliveryResult
    {
        // 1. Check if sponsor has onboarded Telegram
        if (empty($sponsor->telegram_chat_id)) {
            return DeliveryResult::skipped("Sponsor has not connected Telegram yet (belum menghubungkan Telegram / no Chat ID).");
        }

        $botToken = PlatformSetting::getTelegramBotToken();

        if (empty($botToken)) {
            if (app()->environment('testing')) {
                Log::info("[MOCK] Telegram message sent to chat {$sponsor->telegram_chat_id}: {$payload->subject}");
                return DeliveryResult::success('mock_tg_' . uniqid());
            }

            return DeliveryResult::failure("Telegram Bot Token is not configured on the server. Please set TELEGRAM_BOT_TOKEN in .env or Settings.");
        }

        $endpoint = rtrim(config('rymainder.channels.telegram.endpoint') ?? 'https://api.telegram.org', '/') . "/bot{$botToken}/sendMessage";

        try {
            $response = Http::timeout(10)->post($endpoint, [
                'chat_id' => $sponsor->telegram_chat_id,
                'text' => $payload->messageBody,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful() && $response->json('ok') === true) {
                $messageId = $response->json('result.message_id');
                return DeliveryResult::success((string) $messageId);
            }

            $description = $response->json('description') ?? $response->body();

            // Fallback: If Markdown parse error (e.g. 400 Bad Request with entity parse issue), retry as plain text
            if ($response->status() === 400 && str_contains(strtolower($description), 'parse')) {
                Log::warning("Telegram Markdown parse error, retrying as plain text for sponsor {$sponsor->id}: {$description}");
                $fallbackResponse = Http::timeout(10)->post($endpoint, [
                    'chat_id' => $sponsor->telegram_chat_id,
                    'text' => $payload->messageBody,
                ]);

                if ($fallbackResponse->successful() && $fallbackResponse->json('ok') === true) {
                    $messageId = $fallbackResponse->json('result.message_id');
                    return DeliveryResult::success((string) $messageId);
                }

                $description = $fallbackResponse->json('description') ?? $fallbackResponse->body();
            }

            return DeliveryResult::failure("Telegram API Error: {$description}");
        } catch (Exception $e) {
            return DeliveryResult::failure("Telegram connection failed: " . $e->getMessage());
        }
    }

    public function isConfigured(): bool
    {
        return ! empty(PlatformSetting::getTelegramBotToken());
    }
}
