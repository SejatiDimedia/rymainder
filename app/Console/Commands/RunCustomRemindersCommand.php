<?php

namespace App\Console\Commands;

use App\Domain\Reminder\Actions\DispatchCustomReminderAction;
use App\Domain\Reminder\Models\CustomReminder;
use Illuminate\Console\Command;

class RunCustomRemindersCommand extends Command
{
    protected $signature = 'custom-reminders:run {--id= : Run a specific custom reminder by ID immediately}';

    protected $description = 'Evaluate and trigger scheduled custom reminders according to their configured schedules';

    public function handle(DispatchCustomReminderAction $dispatchAction): int
    {
        $specificId = $this->option('id');

        if ($specificId) {
            $reminder = CustomReminder::find($specificId);
            if (! $reminder) {
                $this->error("Custom reminder with ID {$specificId} not found.");
                return Command::FAILURE;
            }

            $this->info("Running Custom Reminder '{$reminder->title}' (ID: {$reminder->id})...");
            $count = $dispatchAction->execute($reminder);
            $this->info("Successfully dispatched to {$count} targeted sponsors.");
            return Command::SUCCESS;
        }

        $now = now();
        $dueReminders = CustomReminder::where('is_active', true)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', $now)
            ->get();

        if ($dueReminders->isEmpty()) {
            $this->info("No custom reminders due for execution at {$now->toDateTimeString()}.");
            return Command::SUCCESS;
        }

        $this->info("Found {$dueReminders->count()} custom reminder(s) due for dispatch.");

        foreach ($dueReminders as $reminder) {
            $this->line("Processing '{$reminder->title}' [{$reminder->scheduleLabel()}]...");
            $processed = $dispatchAction->execute($reminder);
            $this->info("Dispatched '{$reminder->title}' to {$processed} sponsor(s).");
        }

        $this->info("Custom reminder run finished successfully.");
        return Command::SUCCESS;
    }
}
