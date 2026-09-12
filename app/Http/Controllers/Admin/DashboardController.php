<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Models\ReminderLog;
use App\Domain\Reminder\Models\ReminderSetting;
use App\Domain\Reminder\Services\DueDateCalculator;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DueDateCalculator $calculator): View
    {
        $today = now()->startOfDay();

        // 1. Core KPIs
        $totalActiveSponsors = Sponsor::where('status', SponsorStatus::ACTIVE)->count();
        $totalPausedSponsors = Sponsor::where('status', SponsorStatus::PAUSED)->count();

        // Fetch all active sponsors to compute due status distribution
        $activeSponsors = Sponsor::where('status', SponsorStatus::ACTIVE)->get();

        $dueSoonCount = 0;
        $overdueCount = 0;
        $upcomingList = [];

        foreach ($activeSponsors as $sponsor) {
            $nextDue = $sponsor->getNextDueDate($today);
            $daysDiff = $calculator->calculateDaysDifference($nextDue, $today);

            if ($daysDiff < 0) {
                $overdueCount++;
            } elseif ($daysDiff <= 7) {
                $dueSoonCount++;
            }

            $upcomingList[] = [
                'sponsor' => $sponsor,
                'next_due' => $nextDue,
                'days_diff' => $daysDiff,
                'status_type' => $daysDiff < 0 ? 'overdue' : ($daysDiff <= 7 ? 'due_soon' : 'normal'),
            ];
        }

        // Sort upcoming sponsors: overdue first, then nearest due date
        usort($upcomingList, function ($a, $b) {
            return $a['days_diff'] <=> $b['days_diff'];
        });

        // Top 10 priority sponsors
        $prioritySponsors = array_slice($upcomingList, 0, 10);

        // 2. Today's Delivery Statistics
        $todaySentCount = ReminderLog::where('status', DeliveryStatus::SENT)
            ->whereDate('updated_at', $today)
            ->count();

        $todayFailedCount = ReminderLog::where('status', DeliveryStatus::FAILED)
            ->whereDate('updated_at', $today)
            ->count();

        $todaySkippedCount = ReminderLog::where('status', DeliveryStatus::SKIPPED)
            ->whereDate('updated_at', $today)
            ->count();

        // 3. Active Wave Configuration Count
        $activeWavesCount = ReminderSetting::where('is_active', true)->count();

        // 4. Recent Delivery Logs
        $recentLogs = ReminderLog::with(['sponsor', 'reminderSetting'])
            ->latest('id')
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'totalActiveSponsors' => $totalActiveSponsors,
            'totalPausedSponsors' => $totalPausedSponsors,
            'dueSoonCount' => $dueSoonCount,
            'overdueCount' => $overdueCount,
            'todaySentCount' => $todaySentCount,
            'todayFailedCount' => $todayFailedCount,
            'todaySkippedCount' => $todaySkippedCount,
            'activeWavesCount' => $activeWavesCount,
            'prioritySponsors' => $prioritySponsors,
            'recentLogs' => $recentLogs,
        ]);
    }
}
