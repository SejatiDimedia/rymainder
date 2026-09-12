<?php

namespace App\Console\Commands;

use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Models\ReminderLog;
use App\Domain\Reminder\Models\ReminderSetting;
use App\Domain\Reminder\Services\DueDateCalculator;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use App\Jobs\SendAdminDailyDigestJob;
use App\Jobs\SendSponsorReminderJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendScheduledRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send 
                            {--wave= : Specific Reminder Setting ID to evaluate and dispatch}
                            {--date= : Custom reference date in YYYY-MM-DD format for simulation/testing}
                            {--dry-run : Simulate execution without dispatching queue jobs}';

    /**
     * The console command description.
     */
    protected $description = 'Evaluate sponsor due dates and dispatch automated reminder wave jobs to the queue';

    public function handle(DueDateCalculator $calculator): int
    {
        $dateOption = $this->option('date');
        $waveOption = $this->option('wave');
        $referenceDate = $dateOption ? Carbon::parse($dateOption)->startOfDay() : now()->startOfDay();
        $isDryRun = (bool) $this->option('dry-run');

        $this->info("==========================================================");
        $this->info(" Rymainder Automated Sponsor Reminder Engine");
        $this->info(" Reference Date : " . $referenceDate->translatedFormat('l, d F Y'));
        if ($waveOption) {
            $this->info(" Target Wave ID : " . $waveOption);
        }
        $this->info(" Mode           : " . ($isDryRun ? "DRY-RUN (No jobs queued)" : "PRODUCTION (Queue Dispatch)"));
        $this->info("==========================================================");

        // 1. Fetch active reminder wave settings
        $query = ReminderSetting::where('is_active', true);
        if (! empty($waveOption)) {
            $query->where('id', $waveOption);
        }
        $activeSettings = $query->get();

        if ($activeSettings->isEmpty()) {
            $this->warn("No active reminder wave settings configured in reminder_settings table. Exiting.");
            return Command::SUCCESS;
        }

        $this->line("Loaded <info>{$activeSettings->count()}</info> active wave setting(s):");
        foreach ($activeSettings as $s) {
            $channelsStr = implode(', ', $s->channels ?? []);
            $this->line(" - [ID {$s->id}] {$s->label} (Days before due: {$s->days_before_due}) -> Channels: [{$channelsStr}]");
        }
        $this->newLine();

        // 2. Stream active sponsors with lazy chunking for O(1) memory scalability
        $totalActive = Sponsor::where('status', SponsorStatus::ACTIVE)->count();
        $this->info("Processing {$totalActive} active sponsor(s)...");

        $dispatchedCount = 0;
        $alreadySentCount = 0;
        $matchedSponsorsCount = 0;

        $cursor = Sponsor::where('status', SponsorStatus::ACTIVE)->lazyById(200);

        foreach ($cursor as $sponsor) {
            $nextDueDate = $sponsor->getNextDueDate($referenceDate);
            $daysDiff = $calculator->calculateDaysDifference($nextDueDate, $referenceDate);

            foreach ($activeSettings as $setting) {
                if ($calculator->matchesWave($nextDueDate, $setting->days_before_due, $referenceDate)) {
                    $matchedSponsorsCount++;
                    $enabledChannels = $setting->getChannelEnums();

                    foreach ($enabledChannels as $channel) {
                        // Verify sponsor channel preferences
                        if (! $sponsor->isChannelEnabled($channel)) {
                            continue;
                        }

                        // Check if already sent
                        $alreadySent = ReminderLog::where('sponsor_id', $sponsor->id)
                            ->whereDate('due_date', $nextDueDate->toDateString())
                            ->where('reminder_setting_id', $setting->id)
                            ->where('channel', $channel->value)
                            ->where('status', DeliveryStatus::SENT)
                            ->exists();

                        if ($alreadySent) {
                            $alreadySentCount++;
                            continue;
                        }

                        if (! $isDryRun) {
                            SendSponsorReminderJob::dispatch(
                                sponsorId: $sponsor->id,
                                reminderSettingId: $setting->id,
                                channel: $channel,
                                dueDateString: $nextDueDate->toDateString()
                            );
                        }

                        $dispatchedCount++;
                        $this->line("  [DISPATCH] Sponsor: {$sponsor->name} | Wave: {$setting->label} | Channel: {$channel->value} | Due: {$nextDueDate->toDateString()}");
                    }
                }
            }
        }

        $this->newLine();
        $this->info("Execution Summary:");
        $this->line(" - Matched Wave Occurrences : <info>{$matchedSponsorsCount}</info>");
        $this->line(" - New Jobs Dispatched      : <info>{$dispatchedCount}</info>");
        $this->line(" - Duplicate Dispatches Prevented (Already Sent): <comment>{$alreadySentCount}</comment>");

        // 3. Queue Daily Digest summary for admin (delayed slightly to allow queue jobs to finish)
        if (! $isDryRun && $dispatchedCount > 0) {
            SendAdminDailyDigestJob::dispatch()->delay(now()->addMinutes(15));
            $this->info("Admin daily digest job scheduled.");
        }

        return Command::SUCCESS;
    }
}
