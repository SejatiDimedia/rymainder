<?php

namespace App\Console\Commands;

use App\Domain\Telegram\Actions\SyncTelegramUpdatesAction;
use Illuminate\Console\Command;

class PollTelegramUpdatesCommand extends Command
{
    protected $signature = 'telegram:poll {--daemon : Keep polling continuously every 5 seconds}';

    protected $description = 'Poll incoming Telegram updates to auto-link sponsors without requiring a public webhook';

    public function handle(SyncTelegramUpdatesAction $syncAction): int
    {
        $this->info("Menjalankan sinkronisasi update Telegram...");

        $isDaemon = $this->option('daemon');

        do {
            $result = $syncAction->execute();

            if ($result['status'] === 'success') {
                if (! empty($result['linked'])) {
                    $this->info("✅ Berhasil menghubungkan " . count($result['linked']) . " sponsor baru!");
                } else {
                    $this->line("Sinkronisasi selesai. Diproses: {$result['processed']} update.");
                }
            } else {
                $this->warn("⚠️  " . $result['message']);
            }

            if ($isDaemon) {
                sleep(5);
            }
        } while ($isDaemon);

        return self::SUCCESS;
    }
}
