<?php

namespace App\Console\Commands;

use App\Domain\Reminder\Actions\RetryReminderLogAction;
use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Models\ReminderLog;
use Illuminate\Console\Command;

class RetryFailedRemindersCommand extends Command
{
    protected $signature = 'reminders:retry-failed
                            {--channel= : Filter by channel (email, whatsapp, telegram)}
                            {--limit=100 : Maximum number of failed reminders to retry}
                            {--dry-run : Preview candidate reminders without dispatching}';

    protected $description = 'Retry undelivered or failed reminder logs across all channels and reminder types';

    public function handle(RetryReminderLogAction $retryAction): int
    {
        $this->info('Scanning for failed reminder deliveries...');

        $query = ReminderLog::where('status', DeliveryStatus::FAILED)
            ->with(['sponsor', 'reminderSetting', 'customReminder'])
            ->orderBy('id', 'asc');

        if ($channel = $this->option('channel')) {
            $query->where('channel', $channel);
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $failedLogs = $query->get();

        if ($failedLogs->isEmpty()) {
            $this->info('No failed reminder logs found.');
            return self::SUCCESS;
        }

        $rows = $failedLogs->map(function (ReminderLog $log) {
            $type = match (true) {
                $log->custom_reminder_id !== null => 'Custom Broadcast',
                $log->is_manual => 'Manual Direct',
                default => $log->reminderSetting?->label ?? 'Standard Wave',
            };

            return [
                'id' => $log->id,
                'sponsor' => $log->sponsor?->name ?? '[Deleted Sponsor]',
                'channel' => $log->channel->label(),
                'type' => $type,
                'due_date' => $log->due_date?->toDateString() ?? '—',
                'error' => \Illuminate\Support\Str::limit($log->error_message ?? '—', 45),
            ];
        })->toArray();

        $this->table(['Log ID', 'Sponsor', 'Channel', 'Type', 'Target Due', 'Last Error'], $rows);

        if ($this->option('dry-run')) {
            $this->warn("[DRY RUN] Found {$failedLogs->count()} failed reminder(s). No retries were dispatched.");
            return self::SUCCESS;
        }

        $this->info("Dispatching retries for {$failedLogs->count()} reminder(s)...");

        $queued = 0;
        foreach ($failedLogs as $log) {
            if ($retryAction->execute($log)) {
                $queued++;
            }
        }

        $this->info("✓ Successfully queued {$queued} of {$failedLogs->count()} failed reminder(s) for worker execution.");

        return self::SUCCESS;
    }
}
