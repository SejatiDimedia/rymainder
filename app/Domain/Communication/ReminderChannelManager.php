<?php

namespace App\Domain\Communication;

use App\Domain\Communication\Contracts\ReminderChannelDriverInterface;
use App\Domain\Communication\Drivers\EmailChannelDriver;
use App\Domain\Communication\Drivers\SmsPlaceholderChannelDriver;
use App\Domain\Communication\Drivers\TelegramBotChannelDriver;
use App\Domain\Communication\Drivers\WhatsAppCloudChannelDriver;
use App\Domain\Reminder\Enums\ReminderChannel;
use InvalidArgumentException;

class ReminderChannelManager
{
    /**
     * @var array<string, ReminderChannelDriverInterface>
     */
    protected array $drivers = [];

    public function __construct()
    {
        $this->registerDefaultDrivers();
    }

    protected function registerDefaultDrivers(): void
    {
        $this->registerDriver(new EmailChannelDriver());
        $this->registerDriver(new WhatsAppCloudChannelDriver());
        $this->registerDriver(new TelegramBotChannelDriver());
        $this->registerDriver(new SmsPlaceholderChannelDriver());
    }

    public function registerDriver(ReminderChannelDriverInterface $driver): self
    {
        $this->drivers[$driver->channel()->value] = $driver;
        return $this;
    }

    public function driver(ReminderChannel|string $channel): ReminderChannelDriverInterface
    {
        $key = $channel instanceof ReminderChannel ? $channel->value : $channel;

        if (! isset($this->drivers[$key])) {
            throw new InvalidArgumentException("Driver not registered for reminder channel: '{$key}'");
        }

        return $this->drivers[$key];
    }

    /**
     * @return array<string, ReminderChannelDriverInterface>
     */
    public function getAvailableDrivers(): array
    {
        return $this->drivers;
    }
}
