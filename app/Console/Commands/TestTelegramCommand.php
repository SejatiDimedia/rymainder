<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestTelegramCommand extends Command
{
    protected $signature = 'telegram:test {chat_id? : Target Telegram Chat ID} {--message= : Custom message to send}';

    protected $description = 'Test Telegram Bot configuration, inspect recent chat IDs, and send a test message';

    public function handle(): int
    {
        $this->info("--- Pengujian Integrasi Telegram Bot Rymainder ---");

        $botToken = config('rymainder.channels.telegram.bot_token');
        $botUsername = config('rymainder.channels.telegram.bot_username');
        $endpoint = rtrim(config('rymainder.channels.telegram.endpoint', 'https://api.telegram.org'), '/');

        if (empty($botToken)) {
            $this->error("❌ TELEGRAM_BOT_TOKEN belum diisi di file .env!");
            $this->line("Silakan buat bot melalui @BotFather di Telegram, lalu tambahkan ke .env:");
            $this->line("  TELEGRAM_BOT_TOKEN=your_token_here");
            $this->line("  TELEGRAM_BOT_USERNAME=your_bot_username");
            return self::FAILURE;
        }

        // 1. Verifikasi kredensial bot dengan memanggil getMe
        $this->line("1. Memeriksa kredensial bot ke Telegram API...");
        try {
            $meResponse = Http::timeout(10)->get("{$endpoint}/bot{$botToken}/getMe");

            if (! $meResponse->successful() || $meResponse->json('ok') !== true) {
                $err = $meResponse->json('description') ?? $meResponse->body();
                $this->error("❌ Gagal terhubung ke bot: {$err}");
                return self::FAILURE;
            }

            $botData = $meResponse->json('result');
            $this->info("✅ Bot Aktif: {$botData['first_name']} (@{$botData['username']}) [ID: {$botData['id']}]");
        } catch (\Exception $e) {
            $this->error("❌ Gagal koneksi internet ke Telegram API: " . $e->getMessage());
            return self::FAILURE;
        }

        $chatId = $this->argument('chat_id');

        // 2. Jika chat_id belum diisi, periksa getUpdates untuk membantu user menemukan Chat ID
        if (empty($chatId)) {
            $this->newLine();
            $this->line("2. Mencari pesan masuk terbaru via getUpdates...");
            try {
                $updatesResponse = Http::timeout(10)->get("{$endpoint}/bot{$botToken}/getUpdates");
                $updates = $updatesResponse->json('result', []);

                if (! empty($updates)) {
                    $this->info("Ditemukan " . count($updates) . " pesan/interaksi terakhir:");
                    $foundChatIds = [];
                    foreach (array_reverse($updates) as $up) {
                        $msg = $up['message'] ?? $up['channel_post'] ?? null;
                        if (! $msg) {
                            continue;
                        }
                        $senderChat = $msg['chat']['id'] ?? null;
                        if (! $senderChat || in_array($senderChat, $foundChatIds)) {
                            continue;
                        }
                        $foundChatIds[] = $senderChat;
                        $senderName = trim(($msg['chat']['first_name'] ?? '') . ' ' . ($msg['chat']['last_name'] ?? ''));
                        $senderUser = $msg['chat']['username'] ?? '-';
                        $text = $msg['text'] ?? '[Media/Lainnya]';

                        $this->line("  • Chat ID: <fg=yellow>{$senderChat}</> | Pengirim: {$senderName} (@{$senderUser}) | Pesan: \"{$text}\"");
                    }

                    if (! empty($foundChatIds)) {
                        $this->newLine();
                        $this->line("💡 Anda dapat menggunakan salah satu Chat ID di atas untuk pengujian!");
                        $this->line("   Contoh kirim tes: <fg=green>php artisan telegram:test {$foundChatIds[0]}</>");
                        $this->line("   Atau masukkan Chat ID tersebut ke profil sponsor di menu Sponsor > Edit.");
                    }
                } else {
                    $this->warn("⚠️  Belum ada pesan masuk ke bot.");
                    $this->line("Langkah selanjutnya:");
                    $this->line("  1. Buka aplikasi Telegram Anda.");
                    $this->line("  2. Cari bot Anda: @{$botData['username']}");
                    $this->line("  3. Klik tombol 'START' atau kirim pesan teks sembarang (misal: 'halo').");
                    $this->line("  4. Jalankan kembali perintah ini: <fg=green>php artisan telegram:test</>");
                }
            } catch (\Exception $e) {
                $this->warn("Catatan: Tidak dapat mengambil getUpdates (" . $e->getMessage() . ").");
            }

            return self::SUCCESS;
        }

        // 3. Jika chat_id diberikan, kirim pesan tes langsung
        $this->newLine();
        $this->line("2. Mengirim pesan tes ke Chat ID: {$chatId}...");
        $customText = $this->option('message') ?: "🔔 *Tes Notifikasi Telegram Rymainder*\n\nAlhamdulillah, bot Telegram Rymainder berhasil terhubung dan siap mengirimkan pengingat donasi!\n\nWaktu pengiriman: " . now()->toDayDateTimeString();

        try {
            $sendResponse = Http::timeout(10)->post("{$endpoint}/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $customText,
                'parse_mode' => 'Markdown',
            ]);

            if ($sendResponse->successful() && $sendResponse->json('ok') === true) {
                $msgId = $sendResponse->json('result.message_id');
                $this->info("✅ Pesan berhasil terkirim ke Telegram! (Message ID: {$msgId})");
                return self::SUCCESS;
            }

            $errorDesc = $sendResponse->json('description') ?? $sendResponse->body();
            $this->error("❌ Gagal mengirim pesan ke Telegram: {$errorDesc}");
            if (str_contains(strtolower($errorDesc), 'chat not found') || str_contains(strtolower($errorDesc), 'bot was blocked')) {
                $this->line("💡 Pastikan Anda sudah membuka bot @{$botData['username']} di Telegram dan telah menekan 'START'.");
            }

            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error("❌ Terjadi kesalahan saat mengirim: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
