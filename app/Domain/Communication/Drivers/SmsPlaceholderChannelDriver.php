<?php

namespace App\Domain\Communication\Drivers;

use App\Domain\Communication\Contracts\ReminderChannelDriverInterface;
use App\Domain\Communication\DataTransferObjects\DeliveryResult;
use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Sponsor\Models\Sponsor;
use App\Domain\Sponsor\ValueObjects\PhoneNumber;
use Exception;
use Illuminate\Support\Facades\Log;

class SmsPlaceholderChannelDriver implements ReminderChannelDriverInterface
{
    public function channel(): ReminderChannel
    {
        return ReminderChannel::SMS;
    }

    public function send(Sponsor $sponsor, NotificationPayload $payload): DeliveryResult
    {
        $config = config('rymainder.channels.sms');

        if (! ($config['enabled'] ?? false)) {
            return DeliveryResult::skipped("Channel SMS belum diaktifkan (SMS_ENABLED=false).");
        }

        if (empty($sponsor->phone)) {
            return DeliveryResult::failure("Nomor telepon sponsor kosong.");
        }

        try {
            $phone = PhoneNumber::from($sponsor->phone);
        } catch (Exception $e) {
            return DeliveryResult::failure("Nomor telepon tidak valid: " . $e->getMessage());
        }

        if (app()->environment('local', 'testing')) {
            Log::info("[MOCK SMS] Sent SMS to {$phone}: {$payload->subject}");
            return DeliveryResult::success('mock_sms_' . uniqid());
        }

        // When enabled in production with Twilio / local SMS provider credentials:
        return DeliveryResult::failure("Provider SMS belum diintegrasikan dengan API key yang valid.");
    }

    public function isConfigured(): bool
    {
        $config = config('rymainder.channels.sms');
        return ($config['enabled'] ?? false) && ! empty($config['api_key']);
    }
}
