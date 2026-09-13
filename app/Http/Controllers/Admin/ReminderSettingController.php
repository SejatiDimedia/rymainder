<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\ReminderSetting;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReminderSettingRequest;
use App\Http\Requests\UpdateReminderSettingRequest;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReminderSettingController extends Controller
{
    public function index(): View
    {
        $settings = ReminderSetting::orderByDesc('days_before_due')->get();
        $dispatchTime = PlatformSetting::get('reminder_dispatch_time', env('REMINDER_DISPATCH_TIME', '07:00'));

        return view('admin.settings.index', [
            'settings' => $settings,
            'dispatchTime' => $dispatchTime,
            'availableChannels' => [
                ReminderChannel::EMAIL,
                ReminderChannel::WHATSAPP,
                ReminderChannel::TELEGRAM,
            ],
        ]);
    }

    public function updateScheduleTime(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reminder_dispatch_time' => ['required', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
        ]);

        PlatformSetting::set('reminder_dispatch_time', $validated['reminder_dispatch_time']);

        $tzLabel = PlatformSetting::getTimezoneLabel();

        return redirect()->route('admin.settings.index')
            ->with('success', "Daily automated dispatch time updated to {$validated['reminder_dispatch_time']} {$tzLabel}.");
    }

    public function store(StoreReminderSettingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $setting = ReminderSetting::create($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', "New reminder wave '{$setting->label}' created successfully.");
    }

    public function update(UpdateReminderSettingRequest $request, ReminderSetting $setting): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $setting->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', "Reminder wave '{$setting->label}' updated successfully.");
    }

    public function destroy(ReminderSetting $setting): RedirectResponse
    {
        $label = $setting->label;
        $setting->delete();

        return redirect()->route('admin.settings.index')
            ->with('success', "Reminder wave '{$label}' deleted successfully.");
    }
}
