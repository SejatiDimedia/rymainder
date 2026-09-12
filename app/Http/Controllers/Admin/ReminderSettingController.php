<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Models\ReminderSetting;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReminderSettingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReminderSettingController extends Controller
{
    public function index(): View
    {
        $settings = ReminderSetting::orderByDesc('days_before_due')->get();

        return view('admin.settings.index', [
            'settings' => $settings,
            'availableChannels' => [
                ReminderChannel::EMAIL,
                ReminderChannel::WHATSAPP,
                ReminderChannel::TELEGRAM,
            ],
        ]);
    }

    public function update(UpdateReminderSettingRequest $request, ReminderSetting $setting): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $setting->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', "Gelombang reminder '{$setting->label}' berhasil diperbarui.");
    }
}
