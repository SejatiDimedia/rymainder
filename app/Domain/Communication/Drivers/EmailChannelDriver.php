<?php

namespace App\Domain\Communication\Drivers;

use App\Domain\Communication\Contracts\ReminderChannelDriverInterface;
use App\Domain\Communication\DataTransferObjects\DeliveryResult;
use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Sponsor\Models\Sponsor;
use App\Mail\SponsorPaymentReminderMail;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailChannelDriver implements ReminderChannelDriverInterface
{
    public function channel(): ReminderChannel
    {
        return ReminderChannel::EMAIL;
    }

    public function send(Sponsor $sponsor, NotificationPayload $payload): DeliveryResult
    {
        if (empty($sponsor->email) || ! filter_var($sponsor->email, FILTER_VALIDATE_EMAIL)) {
            return DeliveryResult::failure("Format alamat email sponsor tidak valid: '{$sponsor->email}'");
        }

        try {
            Mail::to($sponsor->email)->send(new SponsorPaymentReminderMail($payload));

            return DeliveryResult::success();
        } catch (Exception $e) {
            Log::error("Email delivery failed for sponsor {$sponsor->id}: " . $e->getMessage());
            return DeliveryResult::failure("Gagal mengirim email: " . $e->getMessage());
        }
    }

    public function isConfigured(): bool
    {
        return ! empty(config('mail.default'));
    }
}
