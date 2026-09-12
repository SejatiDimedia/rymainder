<?php

namespace App\Domain\Reminder\Actions;

use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\ReminderLog;
use App\Domain\Reminder\Models\ReminderSetting;
use App\Domain\Sponsor\Models\Sponsor;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ReserveReminderDeliveryAction
{
    /**
     * Atomically reserve or check a delivery attempt in the database.
     *
     * Returns:
     * - ReminderLog instance if safe to proceed (newly created PENDING, or retry of FAILED)
     * - null if delivery already succeeded (SENT) or was skipped (SKIPPED) to strictly prevent duplicates
     */
    public function execute(
        Sponsor $sponsor,
        ReminderSetting $setting,
        ReminderChannel $channel,
        CarbonInterface $dueDate
    ): ?ReminderLog {
        $dateStr = $dueDate->toDateString();

        try {
            // First, find or create the log entry atomically using the unique constraint
            $log = ReminderLog::firstOrCreate(
                [
                    'sponsor_id' => $sponsor->id,
                    'due_date' => $dateStr,
                    'reminder_setting_id' => $setting->id,
                    'channel' => $channel->value,
                ],
                [
                    'status' => DeliveryStatus::PENDING,
                ]
            );

            // If already sent, do NOT send again (Zero Double-Send Guarantee)
            if ($log->status === DeliveryStatus::SENT) {
                Log::info("Duplicate reminder prevented for sponsor {$sponsor->id}, wave {$setting->id}, channel {$channel->value}, due {$dateStr}.");
                return null;
            }

            // If was previously skipped (e.g. Telegram chat_id was missing), but now chat_id is present, allow retry
            if ($log->status === DeliveryStatus::SKIPPED) {
                if ($channel === ReminderChannel::TELEGRAM && ! empty($sponsor->telegram_chat_id)) {
                    $log->update(['status' => DeliveryStatus::PENDING, 'error_message' => null]);
                    return $log;
                }
                return null;
            }

            return $log;
        } catch (QueryException $e) {
            // In case of high concurrency race condition hitting unique constraint
            Log::warning("Race condition caught on reminder log reservation: " . $e->getMessage());
            return null;
        }
    }
}
