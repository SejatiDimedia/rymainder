<?php

namespace App\Domain\Reminder\Enums;

enum ReminderChannel: string
{
    case EMAIL = 'email';
    case WHATSAPP = 'whatsapp';
    case TELEGRAM = 'telegram';
    case SMS = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'Email (SMTP)',
            self::WHATSAPP => 'WhatsApp',
            self::TELEGRAM => 'Telegram',
            self::SMS => 'SMS (Fallback)',
        };
    }

    public function iconName(): string
    {
        return match ($this) {
            self::EMAIL => 'mail',
            self::WHATSAPP => 'message-circle',
            self::TELEGRAM => 'send',
            self::SMS => 'smartphone',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::EMAIL => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800',
            self::WHATSAPP => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
            self::TELEGRAM => 'bg-sky-50 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300 border border-sky-200 dark:border-sky-800',
            self::SMS => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-200 dark:border-purple-800',
        };
    }
}
