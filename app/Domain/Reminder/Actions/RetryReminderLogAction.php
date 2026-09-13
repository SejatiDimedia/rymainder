<?php

namespace App\Domain\Reminder\Actions;

use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Models\ReminderLog;
use App\Jobs\SendCustomReminderJob;
use App\Jobs\SendManualReminderJob;
use App\Jobs\SendSponsorReminderJob;
use Illuminate\Support\Facades\Log;

class RetryReminderLogAction
{
    /**
     * Re-dispatch an undelivered or failed reminder log regardless of reminder type.
     *
     * Supports:
     * 1. Standard cycle wave reminders (SendSponsorReminderJob)
     * 2. Custom announcement / broadcast reminders (SendCustomReminderJob)
     * 3. Manual direct message reminders (SendManualReminderJob)
     */
    public function execute(ReminderLog $log): bool
    {
        $sponsor = $log->sponsor;
        if (! $sponsor) {
            Log::warning("Cannot retry reminder log {$log->id}: Sponsor record does not exist.");
            return false;
        }

        // Reset log state to PENDING and clear previous errors
        $log->update([
            'status' => DeliveryStatus::PENDING,
            'error_message' => null,
        ]);

        // 1. Standard Wave Reminder
        if ($log->reminder_setting_id && $log->reminderSetting) {
            SendSponsorReminderJob::dispatch(
                sponsorId: $sponsor->id,
                reminderSettingId: $log->reminder_setting_id,
                channel: $log->channel,
                dueDateString: $log->due_date->format('Y-m-d')
            );
            return true;
        }

        // 2. Custom Broadcast Reminder
        if ($log->custom_reminder_id && $log->customReminder) {
            SendCustomReminderJob::dispatch(
                sponsorId: $sponsor->id,
                customReminderId: $log->custom_reminder_id,
                channel: $log->channel
            );
            return true;
        }

        // 3. Manual Direct Reminder or Custom Message
        if ($log->is_manual || ! empty($log->custom_message)) {
            SendManualReminderJob::dispatch(
                reminderLogId: $log->id
            );
            return true;
        }

        Log::warning("Cannot retry reminder log {$log->id}: Unrecognized reminder configuration.");
        return false;
    }
}
