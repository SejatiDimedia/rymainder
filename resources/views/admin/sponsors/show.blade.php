<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                        {{ $sponsor->name }}
                    </h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap {{ $sponsor->status->badgeClasses() }}">
                        {{ $sponsor->status->label() }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Donor profile details, Telegram bot onboarding status, and reminder delivery audits
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a 
                    href="{{ route('admin.sponsors.index') }}" 
                    class="px-3.5 py-2 border border-slate-200 bg-white rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-50 transition shadow-xs whitespace-nowrap"
                >
                    ← Sponsors List
                </a>
                <a 
                    href="{{ route('admin.sponsors.edit', $sponsor) }}" 
                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] whitespace-nowrap"
                >
                    Edit Profile
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- 1. Profile Details & Donation Cycle (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <h2 class="text-sm font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100">
                        Donation & Contact Information
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px] mb-1">Email Address</span>
                            <span class="font-semibold text-slate-800">{{ $sponsor->email }}</span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px] mb-1">WhatsApp / Phone Number</span>
                            <span class="font-semibold text-slate-800">{{ $sponsor->phone }}</span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px] mb-1">Beneficiary Name</span>
                            <span class="font-semibold text-slate-800">{{ $sponsor->orphan_name ?? 'General Fund' }}</span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px] mb-1">Donation Commitment</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $sponsor->formatted_amount }}</span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px] mb-1">Payment Frequency</span>
                            <span class="font-semibold text-slate-800">{{ $sponsor->frequency->label() }}</span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px] mb-1">Last Donation Date</span>
                            <span class="font-semibold text-slate-800">{{ $sponsor->last_donation_date->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>

                    <!-- Channel Preferences -->
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <span class="text-slate-400 block text-[11px] mb-2 font-medium">Notification Channel Preferences</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['email' => 'Email', 'whatsapp' => 'WhatsApp', 'telegram' => 'Telegram'] as $key => $label)
                                @php
                                    $isEnabled = $sponsor->isChannelEnabled(\App\Domain\Reminder\Enums\ReminderChannel::from($key));
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-medium whitespace-nowrap {{ $isEnabled ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-slate-100 text-slate-400 border border-slate-200/60' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isEnabled ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $label }}: {{ $isEnabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    @if($sponsor->notes)
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <span class="text-slate-400 block text-[11px] mb-1 font-medium">Staff Notes</span>
                            <p class="text-xs text-slate-700 bg-slate-50 p-3.5 rounded-xl border border-slate-200/60 leading-relaxed">{{ $sponsor->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. Due Date Status & Telegram Onboarding (1 Col) -->
            <div class="space-y-6">

                <!-- Due Date Box -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 block mb-1">
                        Due Date Status
                    </span>
                    <div class="text-2xl font-bold text-slate-900 tracking-tight">
                        {{ $nextDue->translatedFormat('d F Y') }}
                    </div>

                    <div class="mt-4">
                        @if($daysDiff < 0)
                            <div class="p-3.5 bg-rose-50 border border-rose-200/80 rounded-xl text-rose-800 text-xs font-medium flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                <span>Overdue by <strong>{{ abs($daysDiff) }} days</strong> from schedule.</span>
                            </div>
                        @elseif($daysDiff === 0)
                            <div class="p-3.5 bg-amber-50 border border-amber-200/80 rounded-xl text-amber-800 text-xs font-medium flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                <span>Due <strong>TODAY</strong>.</span>
                            </div>
                        @elseif($daysDiff <= 7)
                            <div class="p-3.5 bg-amber-50 border border-amber-200/80 rounded-xl text-amber-800 text-xs font-medium flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                <span>Due in <strong>{{ $daysDiff }} days</strong>.</span>
                            </div>
                        @else
                            <div class="p-3.5 bg-emerald-50 border border-emerald-200/80 rounded-xl text-emerald-800 text-xs font-medium flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>Due in <strong>{{ $daysDiff }} days</strong>.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Telegram Onboarding Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-slate-900 text-sm">Telegram Bot Activation</h3>
                        @if($sponsor->hasConnectedTelegram())
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Connected
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Pending
                            </span>
                        @endif
                    </div>

                    @if($sponsor->hasConnectedTelegram())
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Telegram account connected with Chat ID: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-800 font-mono text-[11px]">{{ $sponsor->telegram_chat_id }}</code>.
                        </p>
                    @else
                        <p class="text-xs text-slate-600 mb-3 leading-relaxed">
                            Share this onboarding link with the sponsor to link their Telegram app:
                        </p>

                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80 text-[11px] break-all font-mono select-all text-blue-600">
                            {{ $telegramOnboardUrl }}
                        </div>

                        <p class="text-[11px] text-slate-400 mt-2">
                            Unique Code: <strong>{{ $sponsor->telegram_onboard_code }}</strong>
                        </p>
                    @endif
                </div>

            </div>

        </div>

        <!-- 3. Reminder Delivery History -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div>
                <h2 class="font-bold text-slate-900 text-base">Reminder Delivery History</h2>
                <p class="text-xs text-slate-500 mt-0.5">Audit log of all notification dispatches processed for this donor</p>
            </div>

            <div class="border border-slate-200/70 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 border-b border-slate-200/70 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3">Schedule / Wave</th>
                                <th class="px-5 py-3">Channel</th>
                                <th class="px-5 py-3">Target Due Date</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Sent Timestamp</th>
                                <th class="px-5 py-3">Log / Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-3 font-semibold text-slate-800 whitespace-nowrap">
                                        {{ $log->reminderSetting->label ?? 'Custom Wave' }}
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] whitespace-nowrap {{ $log->channel->badgeClasses() }}">
                                            {{ $log->channel->label() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-600 whitespace-nowrap">
                                        {{ $log->due_date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] whitespace-nowrap {{ $log->status->badgeClasses() }}">
                                            {{ $log->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500 whitespace-nowrap">
                                        {{ $log->sent_at ? $log->sent_at->translatedFormat('d M Y, H:i') : '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-slate-500">
                                        {{ $log->error_message ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-slate-400 text-xs">
                                        No reminder delivery records found for this sponsor.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($logs->hasPages())
                <div class="pt-2">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
