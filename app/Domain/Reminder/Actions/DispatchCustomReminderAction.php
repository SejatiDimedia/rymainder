<?php

namespace App\Domain\Reminder\Actions;

use App\Domain\Reminder\Models\CustomReminder;
use App\Jobs\SendCustomReminderJob;
use Illuminate\Support\Facades\Log;

class DispatchCustomReminderAction
{
    /**
     * Dispatch custom reminder to all resolved sponsors across enabled channels.
     *
     * @return int Number of sponsors queued/processed
     */
    public function execute(CustomReminder $customReminder, bool $sync = false): int
    {
        $sponsors = $customReminder->getTargetSponsors();
        $channels = $customReminder->getChannelEnums();

        Log::info("Dispatching Custom Reminder ID {$customReminder->id} ('{$customReminder->title}') to {$sponsors->count()} sponsors.");

        $count = 0;
        foreach ($sponsors as $sponsor) {
            foreach ($channels as $channel) {
                if ($sync) {
                    dispatch_sync(new SendCustomReminderJob($sponsor->id, $customReminder->id, $channel));
                } else {
                    SendCustomReminderJob::dispatch($sponsor->id, $customReminder->id, $channel);
                }
            }
            $count++;
        }

        $now = now();
        $customReminder->update([
            'last_run_at' => $now,
            'next_run_at' => $customReminder->computeNextRunAt($now),
            'total_sent_count' => $customReminder->total_sent_count + $count,
        ]);

        return $count;
    }
}
