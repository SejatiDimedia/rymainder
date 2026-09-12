<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use App\Domain\Communication\ReminderChannelManager;
use App\Domain\Reminder\Actions\RecordReminderDeliveryResultAction;
use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\ReminderLog;
use App\Domain\Reminder\Models\ReminderSetting;
use App\Domain\Reminder\Services\DueDateCalculator;
use App\Domain\Sponsor\Models\Sponsor;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ManualReminderController extends Controller
{
    public function send(
        Request $request,
        Sponsor $sponsor,
        ReminderChannelManager $channelManager,
        RecordReminderDeliveryResultAction $recordAction,
        DueDateCalculator $calculator
    ): RedirectResponse {
        $validated = $request->validate([
            'channel' => ['required', 'string', 'in:email,whatsapp,telegram'],
            'reminder_setting_id' => ['nullable', 'integer', 'exists:reminder_settings,id'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $channelEnum = ReminderChannel::from($validated['channel']);

        // Check channel contact availability
        if ($channelEnum === ReminderChannel::WHATSAPP && empty($sponsor->phone)) {
            return back()->with('error', "Cannot send WhatsApp reminder: Sponsor phone number is empty.");
        }
        if ($channelEnum === ReminderChannel::EMAIL && empty($sponsor->email)) {
            return back()->with('error', "Cannot send Email reminder: Sponsor email address is empty.");
        }
        if ($channelEnum === ReminderChannel::TELEGRAM && empty($sponsor->telegram_chat_id)) {
            return back()->with('error', "Cannot send Telegram reminder: Sponsor has not connected Telegram account yet.");
        }

        $nextDueDate = $sponsor->getNextDueDate();
        $setting = ! empty($validated['reminder_setting_id'])
            ? ReminderSetting::find($validated['reminder_setting_id'])
            : null;

        $log = ReminderLog::create([
            'sponsor_id' => $sponsor->id,
            'due_date' => $nextDueDate->toDateString(),
            'reminder_setting_id' => $setting?->id,
            'channel' => $channelEnum,
            'is_manual' => true,
            'status' => DeliveryStatus::PENDING,
            'custom_message' => $validated['message'],
        ]);

        $daysDiff = $calculator->calculateDaysDifference($nextDueDate);
        $payload = NotificationPayload::make(
            sponsor: $sponsor,
            dueDate: $nextDueDate,
            waveLabel: $setting ? $setting->label : 'Custom Direct Reminder',
            daysDifference: $daysDiff,
            directMessage: $validated['message'],
            isCustom: true,
        );

        try {
            $driver = $channelManager->driver($channelEnum);
            $result = $driver->send($sponsor, $payload);
            $recordAction->execute($log, $result);

            if ($result->isSuccess) {
                return back()->with('success', "Custom reminder successfully dispatched to {$sponsor->name} via {$channelEnum->label()}!");
            }

            if ($result->isSkipped) {
                return back()->with('error', "Reminder delivery skipped: {$result->errorMessage}");
            }

            return back()->with('error', "Reminder send failed: {$result->errorMessage}");
        } catch (Exception $e) {
            Log::error("Manual reminder exception for sponsor {$sponsor->id}: " . $e->getMessage());
            $recordAction->execute($log, \App\Domain\Communication\DataTransferObjects\DeliveryResult::failure($e->getMessage()));
            return back()->with('error', "An unexpected error occurred while sending: " . $e->getMessage());
        }
    }
}
