<?php

namespace App\Jobs;

use App\Domain\Communication\DataTransferObjects\DeliveryResult;
use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use App\Domain\Communication\ReminderChannelManager;
use App\Domain\Reminder\Actions\RecordReminderDeliveryResultAction;
use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\ReminderLog;
use App\Domain\Reminder\Services\DueDateCalculator;
use App\Domain\Sponsor\Enums\SponsorStatus;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendManualReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly int $reminderLogId
    ) {
    }

    public function handle(
        ReminderChannelManager $channelManager,
        RecordReminderDeliveryResultAction $recordAction,
        DueDateCalculator $calculator
    ): void {
        $log = ReminderLog::find($this->reminderLogId);

        if (! $log) {
            Log::warning("ReminderLog ({$this->reminderLogId}) not found for manual reminder job.");
            return;
        }

        $sponsor = $log->sponsor;
        if (! $sponsor) {
            Log::warning("Sponsor not found for manual reminder log {$log->id}.");
            $log->update([
                'status' => DeliveryStatus::FAILED,
                'error_message' => 'Associated sponsor record no longer exists.',
            ]);
            return;
        }

        if ($sponsor->status !== SponsorStatus::ACTIVE) {
            Log::info("Skipping manual reminder for non-active sponsor {$sponsor->id}.");
            $log->update([
                'status' => DeliveryStatus::SKIPPED,
                'error_message' => "Sponsor is not active ({$sponsor->status->value}).",
            ]);
            return;
        }

        // Contact availability check
        if ($log->channel === ReminderChannel::TELEGRAM && empty($sponsor->telegram_chat_id)) {
            $log->update([
                'status' => DeliveryStatus::SKIPPED,
                'error_message' => 'Sponsor has not connected Telegram account yet.',
            ]);
            return;
        }

        if ($log->channel === ReminderChannel::EMAIL && empty($sponsor->email)) {
            $log->update([
                'status' => DeliveryStatus::SKIPPED,
                'error_message' => 'Sponsor email address is empty.',
            ]);
            return;
        }

        if ($log->channel === ReminderChannel::WHATSAPP && empty($sponsor->phone)) {
            $log->update([
                'status' => DeliveryStatus::SKIPPED,
                'error_message' => 'Sponsor phone number is empty.',
            ]);
            return;
        }

        $daysDiff = $calculator->calculateDaysDifference($log->due_date);
        $payload = NotificationPayload::make(
            sponsor: $sponsor,
            dueDate: $log->due_date,
            waveLabel: $log->reminderSetting?->label ?? 'Custom Direct Reminder',
            daysDifference: $daysDiff,
            directMessage: $log->custom_message,
            isCustom: true,
        );

        try {
            $driver = $channelManager->driver($log->channel);
            $result = $driver->send($sponsor, $payload);

            $recordAction->execute($log, $result);

            if (! $result->isSuccess && ! $result->isSkipped) {
                Log::warning("Manual reminder delivery failed for sponsor {$sponsor->id} via {$log->channel->value}: {$result->errorMessage}");
            }
        } catch (Exception $e) {
            Log::error("Exception sending manual reminder to sponsor {$sponsor->id}: " . $e->getMessage());
            $recordAction->execute($log, DeliveryResult::failure($e->getMessage()));
            throw $e;
        }
    }
}
