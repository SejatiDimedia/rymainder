<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reminder\Enums\DeliveryStatus;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\ReminderLog;
use App\Http\Controllers\Controller;
use App\Jobs\SendSponsorReminderJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReminderLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ReminderLog::with(['sponsor', 'reminderSetting']);

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

        return view('admin.logs.index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'status', 'channel', 'date']),
            'statuses' => DeliveryStatus::cases(),
            'channels' => ReminderChannel::cases(),
        ]);
    }

    public function retry(ReminderLog $log): RedirectResponse
    {
        $sponsor = $log->sponsor;
        $setting = $log->reminderSetting;

        if (! $sponsor || ! $setting) {
            return back()->with('error', 'Data sponsor atau pengaturan reminder tidak ditemukan.');
        }

        // Reset log status to pending
        $log->update([
            'status' => DeliveryStatus::PENDING,
            'error_message' => null,
        ]);

        SendSponsorReminderJob::dispatch(
            sponsorId: $sponsor->id,
            reminderSettingId: $setting->id,
            channel: $log->channel,
            dueDateString: $log->due_date->format('Y-m-d')
        );

        return back()->with('success', "Percobaan pengiriman ulang via {$log->channel->label()} telah dimasukkan ke antrean worker.");
    }
}
