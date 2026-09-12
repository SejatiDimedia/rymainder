<?php

namespace App\Domain\Reminder\Actions;

use App\Domain\Communication\DataTransferObjects\DeliveryResult;
use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Models\ReminderLog;

class RecordReminderDeliveryResultAction
{
    public function execute(ReminderLog $log, DeliveryResult $result): ReminderLog
    {
        if ($result->isSuccess) {
            $log->update([
                'status' => DeliveryStatus::SENT,
                'sent_at' => now(),
                'error_message' => null,
            ]);
        } elseif ($result->isSkipped) {
            $log->update([
                'status' => DeliveryStatus::SKIPPED,
                'error_message' => $result->errorMessage,
            ]);
        } else {
            $log->update([
                'status' => DeliveryStatus::FAILED,
                'error_message' => $result->errorMessage,
            ]);
        }

        return $log->fresh();
    }
}
