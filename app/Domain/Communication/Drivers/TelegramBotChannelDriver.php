<?php

namespace App\Domain\Communication\Drivers;

use App\Domain\Communication\Contracts\ReminderChannelDriverInterface;
use App\Domain\Communication\DataTransferObjects\DeliveryResult;
use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Sponsor\Models\Sponsor;
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
            return DeliveryResult::skipped("Sponsor belum menghubungkan Telegram (belum /start ke bot yayasan).");
        }

        $config = config('rymainder.channels.telegram');
        $botToken = $config['bot_token'] ?? null;

        if (empty($botToken)) {
            if (app()->environment('local', 'testing')) {
                Log::info("[MOCK] Telegram message sent to chat {$sponsor->telegram_chat_id}: {$payload->subject}");
                return DeliveryResult::success('mock_tg_' . uniqid());
            }

            return DeliveryResult::failure("Kredensial Telegram Bot Token belum dikonfigurasi di server.");
        }

        try {
            $endpoint = rtrim($config['endpoint'] ?? 'https://api.telegram.org', '/') . "/bot{$botToken}/sendMessage";

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
            return DeliveryResult::failure("Telegram API Error: {$description}");
        } catch (Exception $e) {
            return DeliveryResult::failure("Koneksi Telegram API gagal: " . $e->getMessage());
        }
    }

    public function isConfigured(): bool
    {
        return ! empty(config('rymainder.channels.telegram.bot_token'));
    }
}
