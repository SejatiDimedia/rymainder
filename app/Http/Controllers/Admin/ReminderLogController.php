<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reminder\Actions\RetryReminderLogAction;
use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\ReminderLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReminderLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ReminderLog::with(['sponsor', 'reminderSetting', 'customReminder']);

        // Search sponsor name / email
        if ($search = $request->input('search')) {
            $query->whereHas('sponsor', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter channel
        if ($channel = $request->input('channel')) {
            $query->where('channel', $channel);
        }

        // Filter date
        if ($date = $request->input('date')) {
            $query->whereDate('due_date', $date);
        }

        $logs = $query->latest('id')->paginate(20)->withQueryString();
        $failedCount = ReminderLog::where('status', DeliveryStatus::FAILED)->count();

        return view('admin.logs.index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'status', 'channel', 'date']),
            'statuses' => DeliveryStatus::cases(),
            'channels' => ReminderChannel::cases(),
            'failedCount' => $failedCount,
        ]);
    }

    public function retry(ReminderLog $log, RetryReminderLogAction $retryAction): RedirectResponse
    {
        if (! $log->sponsor) {
            return back()->with('error', 'Sponsor data associated with this reminder was not found.');
        }

        $success = $retryAction->execute($log);

        if (! $success) {
            return back()->with('error', 'Failed to queue retry. The reminder configuration or template is invalid.');
        }

        return back()->with('success', "Retry attempt via {$log->channel->label()} has been queued.");
    }

    public function retryAll(Request $request, RetryReminderLogAction $retryAction): RedirectResponse
    {
        $query = ReminderLog::where('status', DeliveryStatus::FAILED)->with(['sponsor', 'reminderSetting', 'customReminder']);

        if ($channel = $request->input('channel')) {
            $query->where('channel', $channel);
        }

        $failedLogs = $query->get();

        if ($failedLogs->isEmpty()) {
            return back()->with('info', 'No failed reminders found to retry.');
        }

        $queuedCount = 0;
        foreach ($failedLogs as $log) {
            if ($retryAction->execute($log)) {
                $queuedCount++;
            }
        }

        return back()->with('success', "{$queuedCount} failed reminder(s) have been queued for resending.");
    }
}
