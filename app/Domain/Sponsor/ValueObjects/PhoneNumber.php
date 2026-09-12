<?php

namespace App\Domain\Sponsor\ValueObjects;

use InvalidArgumentException;

class PhoneNumber
{
    protected string $e164;

    public function __construct(string $rawNumber)
    {
        $normalized = self::normalize($rawNumber);

        if (! self::isValid($normalized)) {
            throw new InvalidArgumentException("Format nomor telepon tidak valid: '{$rawNumber}'. Wajib menggunakan format E.164 (contoh: +6281234567890).");
        }

        $this->e164 = $normalized;
    }

    public static function from(string $rawNumber): self
    {
        return new self($rawNumber);
    }

    public static function normalize(string $rawNumber): string
    {
        // Strip whitespace, hyphens, parentheses, and dots
        $cleaned = preg_replace('/[\s\-\(\)\.]/', '', trim($rawNumber));

        if (empty($cleaned)) {
            return '';
        }

        // Convert leading '08' to Indonesian international code '+628'
        if (str_starts_with($cleaned, '08')) {
            $cleaned = '+62' . substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '62') && ! str_starts_with($cleaned, '+')) {
            $cleaned = '+' . $cleaned;
        } elseif (! str_starts_with($cleaned, '+')) {
            // If no plus and starts with 8..., assume ID prefix
            if (str_starts_with($cleaned, '8')) {
                $cleaned = '+62' . $cleaned;
            } else {
                $cleaned = '+' . $cleaned;
            }
        }

        return $cleaned;
    }

    public static function isValid(string $number): bool
    {
        // E.164 standard: + followed by 7 to 15 digits
        return (bool) preg_match('/^\+[1-9]\d{6,14}$/', $number);
    }

    public function toE164(): string
    {
        return $this->e164;
    }

    public function toDigitsOnly(): string
    {
        return ltrim($this->e164, '+');
    }

    public function __toString(): string
    {
        return $this->e164;
    }
}
