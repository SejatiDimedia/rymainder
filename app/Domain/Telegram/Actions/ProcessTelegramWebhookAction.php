<?php

namespace App\Domain\Telegram\Actions;

use App\Domain\Sponsor\Models\Sponsor;
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

        // Check if message is a /start command with code: e.g. "/start ABC123XYZ"
        if (preg_match('/^\/start\s+([A-Za-z0-9_\-]+)/i', $text, $matches)) {
            $onboardCode = trim($matches[1]);
            $sponsor = Sponsor::where('telegram_onboard_code', $onboardCode)->first();

            if ($sponsor) {
                // Link telegram_chat_id
                $sponsor->update([
                    'telegram_chat_id' => (string) $chatId,
                ]);

                $replyText = "Assalamu'alaikum Wr. Wb. / Salam Sejahtera,\n\n"
                    . "Alhamdulillah, terima kasih Bpk/Ibu *{$sponsor->name}*!\n\n"
                    . "Akun Telegram Anda telah berhasil terhubung dengan sistem pengingat donasi *Yayasan Peduli Anak Yatim*. "
                    . "Mulai saat ini, jadwal pengingat komitmen donasi rutin Anda akan otomatis dikirimkan ke chat ini.\n\n"
                    . "Semoga Allah SWT membalas segala amal kebaikan Bpk/Ibu dengan keberkahan yang berlipat ganda. Aamiin.";

                $this->sendTelegramReply($chatId, $replyText);

                Log::info("Telegram linked successfully for sponsor {$sponsor->id} with chat_id {$chatId}.");

                return [
                    'status' => 'success',
                    'sponsor_id' => $sponsor->id,
                    'chat_id' => $chatId,
                ];
            } else {
                $replyText = "Kode aktivasi tidak dikenali atau sudah kedaluwarsa. Mohon hubungi admin yayasan untuk mendapatkan link aktivasi yang baru.";
                $this->sendTelegramReply($chatId, $replyText);

                return ['status' => 'invalid_code', 'code' => $onboardCode];
            }
        }

        // Standard /start without code or normal greeting
        if (str_starts_with($text, '/start')) {
            $replyText = "Selamat datang di Bot Resmi Yayasan Peduli Anak Yatim.\n\n"
                . "Untuk menghubungkan akun sponsor/donatur Anda, silakan klik tautan aktivasi khusus yang telah dibagikan oleh staf yayasan, atau hubungi admin kami.";
            $this->sendTelegramReply($chatId, $replyText);

            return ['status' => 'prompt_code'];
        }

        // Default response for other messages
        $replyText = "Terima kasih telah menghubungi kami. Pesan Anda telah kami terima. Untuk informasi lebih lanjut mengenai donasi, silakan hubungi staf yayasan di nomor WhatsApp resmi.";
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
