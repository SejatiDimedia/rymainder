<?php

namespace App\Jobs;

use App\Domain\Communication\DataTransferObjects\DeliveryResult;
use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use App\Domain\Communication\ReminderChannelManager;
use App\Domain\Reminder\Actions\RecordReminderDeliveryResultAction;
use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\CustomReminder;
use App\Domain\Reminder\Models\ReminderLog;
use App\Domain\Reminder\Services\DueDateCalculator;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCustomReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly int $sponsorId,
        public readonly int $customReminderId,
        public readonly ReminderChannel $channel
    ) {
    }

    public function handle(
        ReminderChannelManager $channelManager,
        RecordReminderDeliveryResultAction $recordAction,
        DueDateCalculator $calculator
    ): void {
        $sponsor = Sponsor::find($this->sponsorId);
        $customReminder = CustomReminder::find($this->customReminderId);

        if (! $sponsor || ! $customReminder) {
            Log::warning("Sponsor ({$this->sponsorId}) or Custom Reminder ({$this->customReminderId}) not found.");
            return;
        }

        if ($sponsor->status !== SponsorStatus::ACTIVE) {
            Log::info("Skipping custom reminder for inactive sponsor {$sponsor->id}.");
            return;
        }

        if (! $sponsor->isChannelEnabled($this->channel)) {
            Log::info("Skipping channel {$this->channel->value} as it is disabled for sponsor {$sponsor->id}.");
            return;
        }

        $nextDueDate = $sponsor->getNextDueDate();
        $daysDiff = $calculator->calculateDaysDifference($nextDueDate);

        // Build rendered message payload
        $payload = NotificationPayload::make(
            sponsor: $sponsor,
            dueDate: $nextDueDate,
            waveLabel: $customReminder->title,
            daysDifference: $daysDiff,
            customTemplate: $customReminder->message
        );

        $log = ReminderLog::create([
            'sponsor_id' => $sponsor->id,
            'due_date' => $nextDueDate->toDateString(),
            'custom_reminder_id' => $customReminder->id,
            'channel' => $this->channel,
            'is_manual' => false,
            'status' => DeliveryStatus::PENDING,
            'custom_message' => $payload->messageBody,
        ]);

        try {
            $driver = $channelManager->driver($this->channel);
            $result = $driver->send($sponsor, $payload);

            $recordAction->execute($log, $result);

            if (! $result->isSuccess && ! $result->isSkipped) {
                Log::warning("Custom reminder send failed for sponsor {$sponsor->id} via {$this->channel->value}: {$result->errorMessage}");
            }
        } catch (Exception $e) {
            Log::error("Exception sending custom reminder to sponsor {$sponsor->id}: " . $e->getMessage());
            $recordAction->execute($log, DeliveryResult::failure($e->getMessage()));
            throw $e;
        }
    }
}
