<?php

namespace App\Domain\Communication\Drivers;

use App\Domain\Communication\Contracts\ReminderChannelDriverInterface;
use App\Domain\Communication\DataTransferObjects\DeliveryResult;
use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Sponsor\Models\Sponsor;
use App\Domain\Sponsor\ValueObjects\PhoneNumber;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudChannelDriver implements ReminderChannelDriverInterface
{
    public function channel(): ReminderChannel
    {
        return ReminderChannel::WHATSAPP;
    }

    public function send(Sponsor $sponsor, NotificationPayload $payload): DeliveryResult
    {
        if (empty($sponsor->phone)) {
            return DeliveryResult::failure("Nomor telepon/WhatsApp sponsor kosong.");
        }

        try {
            $phoneNumber = PhoneNumber::from($sponsor->phone);
            $toDigits = $phoneNumber->toDigitsOnly();
        } catch (Exception $e) {
            return DeliveryResult::failure("Nomor WhatsApp sponsor tidak valid: " . $e->getMessage());
        }

        $config = config('rymainder.channels.whatsapp');
        $driver = $config['driver'] ?? 'cloud_api';

        // 1. WhatsApp Cloud API (Meta)
        if ($driver === 'cloud_api') {
            $token = $config['cloud_api']['token'] ?? null;
            $phoneNumberId = $config['cloud_api']['phone_number_id'] ?? null;

            if (empty($token) || empty($phoneNumberId)) {
                // If in testing or local without credentials, log and return simulation
                if (app()->environment('local', 'testing')) {
                    Log::info("[MOCK] WhatsApp Cloud API message sent to {$toDigits}: {$payload->subject}");
                    return DeliveryResult::success('mock_wa_' . uniqid());
                }

                return DeliveryResult::failure("Kredensial WhatsApp Cloud API (Token / Phone Number ID) belum dikonfigurasi.");
            }

            try {
                $endpoint = rtrim($config['cloud_api']['endpoint'], '/') . "/{$phoneNumberId}/messages";

                $response = Http::withToken($token)
                    ->timeout(15)
                    ->post($endpoint, [
                        'messaging_product' => 'whatsapp',
                        'recipient_type' => 'individual',
                        'to' => $toDigits,
                        'type' => 'text',
                        'text' => [
                            'preview_url' => false,
                            'body' => $payload->messageBody,
                        ],
                    ]);

                if ($response->successful()) {
                    $body = $response->json();
                    $messageId = $body['messages'][0]['id'] ?? null;
                    return DeliveryResult::success($messageId);
                }

                $errorData = $response->json('error');
                $errMsg = is_array($errorData) ? ($errorData['message'] ?? json_encode($errorData)) : $response->body();
                // Sanitize error message to ensure tokens are never leaked
                $cleanMsg = preg_replace('/(Bearer\s+|token=)[A-Za-z0-9_\-\.]+/', '$1[REDACTED]', $errMsg);

                return DeliveryResult::failure("WhatsApp Cloud API Error: {$cleanMsg}");
            } catch (Exception $e) {
                return DeliveryResult::failure("Koneksi WhatsApp API gagal: " . $e->getMessage());
            }
        }

        // 2. Local Gateway Provider (e.g. Watzap / Zenziva)
        $providerKey = $config['provider']['api_key'] ?? null;
        $providerEndpoint = $config['provider']['endpoint'] ?? null;

        if (empty($providerKey) || empty($providerEndpoint)) {
            if (app()->environment('local', 'testing')) {
                Log::info("[MOCK] WhatsApp Provider message sent to {$toDigits}");
                return DeliveryResult::success('mock_wa_provider_' . uniqid());
            }

            return DeliveryResult::failure("Kredensial WhatsApp Gateway Provider belum dikonfigurasi.");
        }

        try {
            $response = Http::timeout(15)->post($providerEndpoint, [
                'api_key' => $providerKey,
                'phone' => $toDigits,
                'message' => $payload->messageBody,
            ]);

            if ($response->successful()) {
                return DeliveryResult::success();
            }

            return DeliveryResult::failure("WhatsApp Provider error: " . $response->body());
        } catch (Exception $e) {
            return DeliveryResult::failure("Koneksi WhatsApp Provider gagal: " . $e->getMessage());
        }
    }

    public function isConfigured(): bool
    {
        $config = config('rymainder.channels.whatsapp');
        return ! empty($config['cloud_api']['token']) || ! empty($config['provider']['api_key']);
    }
}
