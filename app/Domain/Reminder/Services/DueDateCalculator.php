<?php

namespace App\Domain\Reminder\Services;

use App\Domain\Sponsor\Enums\PaymentFrequency;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class DueDateCalculator
{
    /**
     * Default window in days for an overdue cycle to remain active before rolling forward
     * to the next upcoming cycle.
     */
    public const DEFAULT_OVERDUE_GRACE_DAYS = 30;

    /**
     * Calculate the active due date for a sponsor based on last donation date,
     * frequency, and a reference date (usually today).
     *
     * If the sponsor is late by multiple cycles (e.g., missed last year and earlier),
     * this rolls forward to the most relevant cycle (either currently within the overdue
     * grace window, or the next upcoming due date).
     */
    public function calculateNextDueDate(
        CarbonInterface|string $lastDonationDate,
        PaymentFrequency $frequency,
        CarbonInterface|string|null $referenceDate = null,
        int $overdueGraceDays = self::DEFAULT_OVERDUE_GRACE_DAYS
    ): Carbon {
        $last = $lastDonationDate instanceof CarbonInterface
            ? $lastDonationDate->copy()->startOfDay()
            : Carbon::parse($lastDonationDate)->startOfDay();

        $ref = $referenceDate instanceof CarbonInterface
            ? $referenceDate->copy()->startOfDay()
            : ($referenceDate ? Carbon::parse($referenceDate)->startOfDay() : now()->startOfDay());

        $monthsToAdd = $frequency->months();

        // Initial cycle
        $dueDate = $last->copy()->addMonthsNoOverflow($monthsToAdd);

        // If the initial due date has already passed beyond the overdue grace window,
        // roll forward in increments of frequency until we reach a cycle that is either:
        // 1) within the overdue window (due_date >= ref - overdueGraceDays), or
        // 2) in the future (due_date >= ref)
        $cutoffDate = $ref->copy()->subDays($overdueGraceDays);

        while ($dueDate->lessThan($cutoffDate)) {
            $dueDate->addMonthsNoOverflow($monthsToAdd);
        }

        return $dueDate;
    }

    /**
     * Calculate days remaining until due date relative to a reference date.
     *
     * Returns:
     *  > 0 : Days before due date (e.g. 7 means H-7)
     *  = 0 : Today is due date (H-0)
     *  < 0 : Days overdue (e.g. -7 means H+7 overdue)
     */
    public function calculateDaysDifference(
        CarbonInterface|string $dueDate,
        CarbonInterface|string|null $referenceDate = null
    ): int {
        $due = $dueDate instanceof CarbonInterface
            ? $dueDate->copy()->startOfDay()
            : Carbon::parse($dueDate)->startOfDay();

        $ref = $referenceDate instanceof CarbonInterface
            ? $referenceDate->copy()->startOfDay()
            : ($referenceDate ? Carbon::parse($referenceDate)->startOfDay() : now()->startOfDay());

        // $ref->diffInDays($due, false) returns positive when $due is in the future,
        // negative when $due is in the past.
        return (int) $ref->diffInDays($due, false);
    }

    /**
     * Check whether a sponsor's due date matches a wave's days_before_due setting.
     *
     * Example:
     * - Wave H-7: days_before_due = 7. Matches if daysDifference == 7.
     * - Wave H-0: days_before_due = 0. Matches if daysDifference == 0.
     * - Wave H+7: days_before_due = -7. Matches if daysDifference == -7.
     */
    public function matchesWave(
        CarbonInterface|string $dueDate,
        int $daysBeforeDue,
        CarbonInterface|string|null $referenceDate = null
    ): bool {
        return $this->calculateDaysDifference($dueDate, $referenceDate) === $daysBeforeDue;
    }
}
