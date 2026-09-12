<?php

namespace App\Domain\Communication\Contracts;

use App\Domain\Communication\DataTransferObjects\DeliveryResult;
use App\Domain\Communication\DataTransferObjects\NotificationPayload;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Sponsor\Models\Sponsor;

interface ReminderChannelDriverInterface
{
    /**
     * The ReminderChannel enum this driver handles.
     */
    public function channel(): ReminderChannel;

    /**
     * Attempt to dispatch notification to the sponsor.
     */
    public function send(Sponsor $sponsor, NotificationPayload $payload): DeliveryResult;

    /**
     * Whether the driver is properly configured with credentials/API keys.
     */
    public function isConfigured(): bool;
}
