<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reminder\Enums\ReminderChannel;
use App\Domain\Reminder\Services\DueDateCalculator;
use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use App\Domain\Telegram\Actions\SyncTelegramUpdatesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSponsorRequest;
use App\Http\Requests\UpdateSponsorRequest;
use App\Mail\TelegramOnboardingInvitationMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SponsorController extends Controller
{
    public function index(Request $request, DueDateCalculator $calculator): View
    {
        $query = Sponsor::query();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('orphan_name', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter frequency
        if ($frequency = $request->input('frequency')) {
            $query->where('frequency', $frequency);
        }

        $sponsors = $query->orderBy('name')->paginate(15)->withQueryString();

        // Attach computed next due date and status badge to each sponsor for view
        $today = now()->startOfDay();
        foreach ($sponsors as $sponsor) {
            $sponsor->computed_next_due = $sponsor->getNextDueDate($today);
            $sponsor->days_diff = $calculator->calculateDaysDifference($sponsor->computed_next_due, $today);
            $sponsor->due_indicator = $sponsor->days_diff < 0 ? 'red' : ($sponsor->days_diff <= 7 ? 'yellow' : 'green');
        }

        return view('admin.sponsors.index', [
            'sponsors' => $sponsors,
            'filters' => $request->only(['search', 'status', 'frequency']),
            'statuses' => SponsorStatus::cases(),
            'frequencies' => PaymentFrequency::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.sponsors.create', [
            'statuses' => SponsorStatus::cases(),
            'frequencies' => PaymentFrequency::cases(),
            'availableChannels' => [
                ReminderChannel::EMAIL,
                ReminderChannel::WHATSAPP,
                ReminderChannel::TELEGRAM,
            ],
        ]);
    }

    public function store(StoreSponsorRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['telegram_onboard_code'] = strtoupper(Str::random(12));

        $sponsor = Sponsor::create($validated);

        return redirect()->route('admin.sponsors.show', $sponsor)
            ->with('success', "Sponsor {$sponsor->name} berhasil didaftarkan ke sistem.");
    }

    public function show(Sponsor $sponsor, DueDateCalculator $calculator): View
    {
        $today = now()->startOfDay();
        $nextDue = $sponsor->getNextDueDate($today);
        $daysDiff = $calculator->calculateDaysDifference($nextDue, $today);

        $logs = $sponsor->reminderLogs()
            ->with('reminderSetting')
            ->latest('id')
            ->paginate(15);

        $settings = \App\Domain\Reminder\Models\ReminderSetting::orderByDesc('days_before_due')->get();

        return view('admin.sponsors.show', [
            'sponsor' => $sponsor,
            'nextDue' => $nextDue,
            'daysDiff' => $daysDiff,
            'logs' => $logs,
            'settings' => $settings,
            'telegramOnboardUrl' => $sponsor->getTelegramOnboardingUrl(),
        ]);
    }

    public function edit(Sponsor $sponsor): View
    {
        return view('admin.sponsors.edit', [
            'sponsor' => $sponsor,
            'statuses' => SponsorStatus::cases(),
            'frequencies' => PaymentFrequency::cases(),
            'availableChannels' => [
                ReminderChannel::EMAIL,
                ReminderChannel::WHATSAPP,
                ReminderChannel::TELEGRAM,
            ],
        ]);
    }

    public function update(UpdateSponsorRequest $request, Sponsor $sponsor): RedirectResponse
    {
        $sponsor->update($request->validated());

        return redirect()->route('admin.sponsors.show', $sponsor)
            ->with('success', "Data sponsor {$sponsor->name} berhasil diperbarui.");
    }

    public function destroy(Sponsor $sponsor): RedirectResponse
    {
        $name = $sponsor->name;
        $sponsor->delete();

        return redirect()->route('admin.sponsors.index')
            ->with('success', "Sponsor {$name} telah dihapus dari sistem.");
    }

    public function sendTelegramInvitation(Sponsor $sponsor): RedirectResponse
    {
        if (empty($sponsor->email)) {
            return back()->with('error', "Sponsor does not have a valid email address.");
        }

        if (empty(config('rymainder.channels.telegram.bot_username'))) {
            return back()->with('error', "Telegram Bot is not configured in system settings.");
        }

        try {
            Mail::to($sponsor->email)->send(new TelegramOnboardingInvitationMail($sponsor));

            return back()->with('success', "Telegram activation invitation successfully dispatched to {$sponsor->email}.");
        } catch (\Exception $e) {
            return back()->with('error', "Failed to send invitation email: " . $e->getMessage());
        }
    }

    public function checkTelegramStatus(Sponsor $sponsor, SyncTelegramUpdatesAction $syncAction): RedirectResponse
    {
        $syncResult = $syncAction->execute();
        $sponsor->refresh();

        if ($sponsor->hasConnectedTelegram()) {
            return back()->with('success', "Telegram account for {$sponsor->name} has been successfully connected (Chat ID: {$sponsor->telegram_chat_id}).");
        }

        $notice = $syncResult['status'] === 'success'
            ? "No activation message received yet from {$sponsor->name}. Make sure the donor opened @".config('rymainder.channels.telegram.bot_username')." and tapped START."
            : "Telegram synchronization failed: " . ($syncResult['message'] ?? 'Please verify bot credentials.');

        return back()->with('info', $notice);
    }

    public function disconnectTelegram(Sponsor $sponsor): RedirectResponse
    {
        $sponsor->update([
            'telegram_chat_id' => null,
        ]);

        return back()->with('success', "Telegram account for sponsor {$sponsor->name} has been disconnected.");
    }
}
