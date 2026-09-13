<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reminder\Actions\DispatchCustomReminderAction;
use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\CustomReminder;
use App\Domain\Sponsor\Models\Sponsor;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomReminderRequest;
use App\Http\Requests\UpdateCustomReminderRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomReminderController extends Controller
{
    public function index(): View
    {
        $reminders = CustomReminder::withCount('sponsors')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.custom-reminders.index', [
            'reminders' => $reminders,
        ]);
    }

    public function create(): View
    {
        $sponsors = Sponsor::orderBy('name')->get();

        return view('admin.custom-reminders.create', [
            'sponsors' => $sponsors,
            'daysOfWeek' => CustomReminder::daysOfWeek(),
            'availableChannels' => [
                ReminderChannel::EMAIL,
                ReminderChannel::WHATSAPP,
                ReminderChannel::TELEGRAM,
            ],
        ]);
    }

    public function store(StoreCustomReminderRequest $request): RedirectResponse
    {
        $times = $this->resolveScheduleTimes($request);

        $reminder = CustomReminder::create([
            'title' => $request->title,
            'message' => $request->message,
            'channels' => $request->channels,
            'target_type' => $request->target_type,
            'schedule_type' => $request->schedule_type,
            'schedule_times' => $times,
            'interval_hours' => $request->schedule_type === 'interval_hours' ? $request->interval_hours : null,
            'schedule_day' => $request->schedule_type === 'weekly' ? $request->schedule_day : null,
            'scheduled_at' => $request->schedule_type === 'once' ? $request->scheduled_at : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->target_type === 'selected' && $request->has('sponsor_ids')) {
            $reminder->sponsors()->sync($request->sponsor_ids);
        }

        $reminder->update([
            'next_run_at' => $reminder->computeNextRunAt(),
        ]);

        return redirect()->route('admin.custom-reminders.index')
            ->with('success', "Custom reminder '{$reminder->title}' created successfully.");
    }

    public function edit(CustomReminder $customReminder): View
    {
        $customReminder->load('sponsors');
        $sponsors = Sponsor::orderBy('name')->get();
        $selectedSponsorIds = $customReminder->sponsors->pluck('id')->all();

        return view('admin.custom-reminders.edit', [
            'reminder' => $customReminder,
            'sponsors' => $sponsors,
            'selectedSponsorIds' => $selectedSponsorIds,
            'daysOfWeek' => CustomReminder::daysOfWeek(),
            'availableChannels' => [
                ReminderChannel::EMAIL,
                ReminderChannel::WHATSAPP,
                ReminderChannel::TELEGRAM,
            ],
        ]);
    }

    public function update(UpdateCustomReminderRequest $request, CustomReminder $customReminder): RedirectResponse
    {
        $times = $this->resolveScheduleTimes($request);

        $data = [
            'title' => $request->title,
            'message' => $request->message,
            'channels' => $request->channels,
            'target_type' => $request->target_type,
            'schedule_type' => $request->schedule_type,
            'schedule_times' => $times,
            'interval_hours' => $request->schedule_type === 'interval_hours' ? $request->interval_hours : null,
            'schedule_day' => $request->schedule_type === 'weekly' ? $request->schedule_day : null,
            'scheduled_at' => $request->schedule_type === 'once' ? $request->scheduled_at : null,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->schedule_type === 'once' && $request->scheduled_at) {
            $data['last_run_at'] = null;
        }

        $customReminder->update($data);

        if ($request->target_type === 'selected' && $request->has('sponsor_ids')) {
            $customReminder->sponsors()->sync($request->sponsor_ids);
        } else {
            $customReminder->sponsors()->detach();
        }

        $customReminder->update([
            'next_run_at' => $customReminder->computeNextRunAt(),
        ]);

        return redirect()->route('admin.custom-reminders.index')
            ->with('success', "Custom reminder '{$customReminder->title}' updated successfully.");
    }

    public function destroy(CustomReminder $customReminder): RedirectResponse
    {
        $title = $customReminder->title;
        $customReminder->delete();

        return redirect()->route('admin.custom-reminders.index')
            ->with('success', "Custom reminder '{$title}' deleted successfully.");
    }

    public function toggle(CustomReminder $customReminder): RedirectResponse
    {
        $newStatus = ! $customReminder->is_active;
        $customReminder->update([
            'is_active' => $newStatus,
            'next_run_at' => $newStatus ? $customReminder->computeNextRunAt() : null,
        ]);

        $statusText = $newStatus ? 'activated' : 'paused';

        return redirect()->route('admin.custom-reminders.index')
            ->with('success', "Custom reminder '{$customReminder->title}' has been {$statusText}.");
    }

    public function runNow(CustomReminder $customReminder, DispatchCustomReminderAction $dispatchAction): RedirectResponse
    {
        $count = $dispatchAction->execute($customReminder, sync: true);

        return redirect()->route('admin.custom-reminders.index')
            ->with('success', "Custom reminder '{$customReminder->title}' successfully dispatched to {$count} targeted sponsor(s)!");
    }

    private function resolveScheduleTimes($request): ?array
    {
        return match ($request->schedule_type) {
            'daily', 'weekly' => $request->schedule_time ? [$request->schedule_time] : ['12:00'],
            'multiple_daily' => array_values(array_filter((array) $request->input('schedule_times', []))),
            default => null,
        };
    }
}
