<?php

use App\Domain\Reminder\Services\DueDateCalculator;
use App\Domain\Sponsor\Enums\PaymentFrequency;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->calculator = new DueDateCalculator();
});

it('calculates annual next due date correctly', function () {
    $lastDonation = Carbon::parse('2025-05-10');
    $refDate = Carbon::parse('2025-06-01');

    $nextDue = $this->calculator->calculateNextDueDate(
        $lastDonation,
        PaymentFrequency::ANNUAL,
        $refDate
    );

    expect($nextDue->toDateString())->toBe('2026-05-10');
});

it('calculates 6-month next due date correctly', function () {
    $lastDonation = Carbon::parse('2025-01-15');
    $refDate = Carbon::parse('2025-03-01');

    $nextDue = $this->calculator->calculateNextDueDate(
        $lastDonation,
        PaymentFrequency::SIX_MONTHS,
        $refDate
    );

    expect($nextDue->toDateString())->toBe('2025-07-15');
});

it('handles month end without overflow (e.g. Aug 31 + 6 months to Feb)', function () {
    $lastDonation = Carbon::parse('2024-08-31');
    $refDate = Carbon::parse('2024-09-01');

    $nextDue = $this->calculator->calculateNextDueDate(
        $lastDonation,
        PaymentFrequency::SIX_MONTHS,
        $refDate
    );

    // 2025 is not a leap year, so Feb has 28 days
    expect($nextDue->toDateString())->toBe('2025-02-28');
});

it('handles leap year leap day (Feb 29) when adding 1 year', function () {
    $lastDonation = Carbon::parse('2024-02-29'); // 2024 is leap year
    $refDate = Carbon::parse('2024-03-01');

    $nextDue = $this->calculator->calculateNextDueDate(
        $lastDonation,
        PaymentFrequency::ANNUAL,
        $refDate
    );

    // 2025 is not a leap year, should cap to Feb 28
    expect($nextDue->toDateString())->toBe('2025-02-28');
});

it('rolls forward when sponsor is late by multiple cycles', function () {
    // Last donation 3 years ago
    $lastDonation = Carbon::parse('2021-03-10');
    $refDate = Carbon::parse('2024-04-15'); // Grace period is 30 days, 2024-03-10 is >30 days overdue

    $nextDue = $this->calculator->calculateNextDueDate(
        $lastDonation,
        PaymentFrequency::ANNUAL,
        $refDate
    );

    // Cycles: 2022-03-10, 2023-03-10, 2024-03-10 (passed > 30 days ago), rolls to 2025-03-10
    expect($nextDue->toDateString())->toBe('2025-03-10');
});

it('retains overdue cycle when within grace window for H+7 reminder', function () {
    $lastDonation = Carbon::parse('2024-01-01');
    // Annual cycle due date is 2025-01-01
    // Today is 2025-01-08 (7 days overdue, within 30 days grace window)
    $refDate = Carbon::parse('2025-01-08');

    $nextDue = $this->calculator->calculateNextDueDate(
        $lastDonation,
        PaymentFrequency::ANNUAL,
        $refDate
    );

    expect($nextDue->toDateString())->toBe('2025-01-01');

    // Verify it matches H+7 (overdue wave where days_before_due = -7)
    $diff = $this->calculator->calculateDaysDifference($nextDue, $refDate);
    expect($diff)->toBe(-7);
    expect($this->calculator->matchesWave($nextDue, -7, $refDate))->toBeTrue();
});

it('calculates days difference correctly for H-7, H-3, H-0, and overdue H+7', function () {
    $dueDate = Carbon::parse('2025-05-20');

    // H-7: 7 days before due date
    expect($this->calculator->calculateDaysDifference($dueDate, '2025-05-13'))->toBe(7);
    expect($this->calculator->matchesWave($dueDate, 7, '2025-05-13'))->toBeTrue();

    // H-3: 3 days before due date
    expect($this->calculator->calculateDaysDifference($dueDate, '2025-05-17'))->toBe(3);
    expect($this->calculator->matchesWave($dueDate, 3, '2025-05-17'))->toBeTrue();

    // H-0: due date today
    expect($this->calculator->calculateDaysDifference($dueDate, '2025-05-20'))->toBe(0);
    expect($this->calculator->matchesWave($dueDate, 0, '2025-05-20'))->toBeTrue();

    // H+7: 7 days after due date (overdue)
    expect($this->calculator->calculateDaysDifference($dueDate, '2025-05-27'))->toBe(-7);
    expect($this->calculator->matchesWave($dueDate, -7, '2025-05-27'))->toBeTrue();
});
