<?php

use App\Domain\Sponsor\ValueObjects\PhoneNumber;

it('normalizes standard Indonesian local phone starting with 08', function () {
    $phone = PhoneNumber::from('081234567890');
    expect($phone->toE164())->toBe('+6281234567890');
    expect($phone->toDigitsOnly())->toBe('6281234567890');
});

it('normalizes phone number with hyphens, spaces, and brackets', function () {
    $phone = PhoneNumber::from('(0812) 3456-7890');
    expect($phone->toE164())->toBe('+6281234567890');
});

it('normalizes phone number starting with 62 without plus', function () {
    $phone = PhoneNumber::from('6281234567890');
    expect($phone->toE164())->toBe('+6281234567890');
});

it('preserves valid E.164 formatted number', function () {
    $phone = PhoneNumber::from('+6281234567890');
    expect($phone->toE164())->toBe('+6281234567890');
});

it('throws InvalidArgumentException for invalid or too short numbers', function () {
    expect(fn () => PhoneNumber::from('123'))
        ->toThrow(InvalidArgumentException::class);
});
