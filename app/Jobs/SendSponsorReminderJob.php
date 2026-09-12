<?php

namespace App\Jobs;

use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use App\Domain\Communication\ReminderChannelManager;
use App\Domain\Reminder\Actions\RecordReminderDeliveryResultAction;
use App\Domain\Reminder\Actions\ReserveReminderDeliveryAction;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\ReminderSetting;
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

class SendSponsorReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly int $sponsorId,
        public readonly int $reminderSettingId,
        public readonly ReminderChannel $channel,
        public readonly string $dueDateString
    ) {
    }

    public function handle(
        ReminderChannelManager $channelManager,
        ReserveReminderDeliveryAction $reserveAction,
        RecordReminderDeliveryResultAction $recordAction,
        DueDateCalculator $calculator
    ): void {
        $sponsor = Sponsor::find($this->sponsorId);
        $setting = ReminderSetting::find($this->reminderSettingId);

        if (! $sponsor || ! $setting) {
            Log::warning("Sponsor ({$this->sponsorId}) or Setting ({$this->reminderSettingId}) not found for reminder job.");
            return;
        }

        if ($sponsor->status !== SponsorStatus::ACTIVE) {
            Log::info("Skipping reminder for non-active sponsor {$sponsor->id} (status: {$sponsor->status->value}).");
            return;
        }

        // Verify sponsor channel preferences
        if (! $sponsor->isChannelEnabled($this->channel)) {
            Log::info("Skipping reminder channel {$this->channel->value} as it is disabled for sponsor {$sponsor->id}.");
            return;
        }

        $dueDate = Carbon::parse($this->dueDateString);

        // 1. Atomic Idempotency Lock: Reserve delivery attempt in database
        $log = $reserveAction->execute($sponsor, $setting, $this->channel, $dueDate);
        if (! $log) {
            // Already sent or duplicate prevented
            return;
        }

        // 2. Build standardized notification payload
        $daysDiff = $calculator->calculateDaysDifference($dueDate);
        $payload = NotificationPayload::make(
            sponsor: $sponsor,
            dueDate: $dueDate,
            waveLabel: $setting->label,
            daysDifference: $daysDiff,
            customTemplate: $setting->message_template,
        );

        // 3. Dispatch via pluggable channel driver
        try {
            $driver = $channelManager->driver($this->channel);
            $result = $driver->send($sponsor, $payload);

            // 4. Record result in audit trail
            $recordAction->execute($log, $result);

            if (! $result->isSuccess && ! $result->isSkipped) {
                Log::warning("Reminder send failed for sponsor {$sponsor->id} via {$this->channel->value}: {$result->errorMessage}");
            }
        } catch (Exception $e) {
            Log::error("Unexpected exception during reminder delivery for sponsor {$sponsor->id}: " . $e->getMessage());
            $recordAction->execute($log, \App\Domain\Communication\DataTransferObjects\DeliveryResult::failure($e->getMessage()));
            throw $e; // Allow queue backoff and retries
        }
    }
}
