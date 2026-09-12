<?php

namespace App\Domain\Sponsor\Enums;

enum PaymentFrequency: string
{
    case ANNUAL = 'annual';
    case SIX_MONTHS = '6_months';

    public function label(): string
    {
        return match ($this) {
            self::ANNUAL => 'Tahunan (12 Bulan)',
            self::SIX_MONTHS => '6 Bulanan',
        };
    }

    public function months(): int
    {
        return match ($this) {
            self::ANNUAL => 12,
            self::SIX_MONTHS => 6,
        };
    }
}
