<?php

namespace App\Domain\Telegram\Actions;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncTelegramUpdatesAction
{
    public function __construct(
        protected ProcessTelegramWebhookAction $processWebhookAction
    ) {
    }

    /**
     * Fetch recent updates from Telegram Bot API getUpdates endpoint
     * and process them as if received via webhook.
     */
    public function execute(): array
    {
        $botToken = \App\Models\PlatformSetting::getTelegramBotToken();
        if (empty($botToken)) {
            return [
                'status' => 'error',
                'message' => 'Telegram Bot Token is not configured on the server.',
                'processed' => 0,
                'linked' => [],
            ];
        }

        $endpoint = rtrim(config('rymainder.channels.telegram.endpoint', 'https://api.telegram.org'), '/');

        try {
            $response = Http::timeout(10)->get("{$endpoint}/bot{$botToken}/getUpdates");

            if (! $response->successful() || $response->json('ok') !== true) {
                $err = $response->json('description') ?? 'Failed to connect to Telegram API.';
                return [
                    'status' => 'error',
                    'message' => "Telegram API: {$err}",
                    'processed' => 0,
                    'linked' => [],
                ];
            }

            $updates = $response->json('result', []);
            $processedCount = 0;
            $newlyLinked = [];

            foreach ($updates as $update) {
                $result = $this->processWebhookAction->execute($update);
                $processedCount++;

                if (($result['status'] ?? null) === 'success' && ! empty($result['sponsor_id'])) {
                    $newlyLinked[] = $result['sponsor_id'];
                }
            }

            return [
                'status' => 'success',
                'message' => "Berhasil memeriksa " . count($updates) . " update dari Telegram.",
                'processed' => $processedCount,
                'linked' => array_unique($newlyLinked),
            ];
        } catch (Exception $e) {
            Log::error("Failed to sync Telegram updates: " . $e->getMessage());

            return [
                'status' => 'error',
                'message' => "Koneksi Telegram gagal: " . $e->getMessage(),
                'processed' => 0,
                'linked' => [],
            ];
        }
    }
}
