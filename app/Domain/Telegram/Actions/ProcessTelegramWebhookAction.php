<?php

namespace App\Domain\Telegram\Actions;

use App\Domain\Sponsor\Models\Sponsor;
use App\Models\PlatformSetting;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessTelegramWebhookAction
{
    public function execute(array $update): array
    {
        $message = $update['message'] ?? null;
        if (! $message) {
            return ['status' => 'ignored', 'reason' => 'No message object in update'];
        }

        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');

        if (! $chatId) {
            return ['status' => 'ignored', 'reason' => 'No chat id found'];
        }

        $platformName = PlatformSetting::getName();

        // Check if message is a /start command with code: e.g. "/start ABC123XYZ"
        if (preg_match('/^\/start\s+([A-Za-z0-9_\-]+)/i', $text, $matches)) {
            $onboardCode = trim($matches[1]);
            $sponsor = Sponsor::where('telegram_onboard_code', $onboardCode)->first();

            if ($sponsor) {
                // Link telegram_chat_id
                $sponsor->update([
                    'telegram_chat_id' => (string) $chatId,
                ]);

                $template = PlatformSetting::getTelegramActivationSuccessMessage();
                $replyText = str_replace(
                    ['{sponsor_name}', '{platform_name}'],
                    [$sponsor->name, $platformName],
                    $template
                );

                $this->sendTelegramReply($chatId, $replyText);

                Log::info("Telegram linked successfully for sponsor {$sponsor->id} with chat_id {$chatId}.");

                return [
                    'status' => 'success',
                    'sponsor_id' => $sponsor->id,
                    'chat_id' => $chatId,
                ];
            } else {
                $template = PlatformSetting::getTelegramInvalidCodeMessage();
                $replyText = str_replace(['{platform_name}'], [$platformName], $template);
                $this->sendTelegramReply($chatId, $replyText);

                return ['status' => 'invalid_code', 'code' => $onboardCode];
            }
        }

        // Standard /start without code or normal greeting
        if (str_starts_with($text, '/start')) {
            $template = PlatformSetting::getTelegramWelcomeMessage();
            $replyText = str_replace(['{platform_name}'], [$platformName], $template);
            $this->sendTelegramReply($chatId, $replyText);

            return ['status' => 'prompt_code'];
        }

        // Default response for other messages
        $template = PlatformSetting::getTelegramDefaultReplyMessage();
        $replyText = str_replace(['{platform_name}'], [$platformName], $template);
        $this->sendTelegramReply($chatId, $replyText);

        return ['status' => 'default_reply'];
    }

    protected function sendTelegramReply(int|string $chatId, string $text): void
    {
        $botToken = config('rymainder.channels.telegram.bot_token');
        if (empty($botToken)) {
            Log::info("[MOCK TELEGRAM REPLY] Chat: {$chatId} | Message: {$text}");
            return;
        }

        try {
            $endpoint = rtrim(config('rymainder.channels.telegram.endpoint', 'https://api.telegram.org'), '/') . "/bot{$botToken}/sendMessage";
            Http::timeout(5)->post($endpoint, [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);
        } catch (Exception $e) {
            Log::error("Failed to send Telegram reply: " . $e->getMessage());
        }
    }
}
