<x-app-layout>
    @php
        $formattedAmount = 'Rp ' . number_format($sponsor->amount, 0, ',', '.');
        $formattedDate = $nextDue->translatedFormat('d F Y');
        $platformName = \App\Models\PlatformSetting::getName();
        $paymentInstructions = \App\Models\PlatformSetting::get('payment_instructions', "• Bank Syariah Indonesia (BSI): 123-456-7890 a.n. {$platformName}\n• Bank Mandiri: 987-654-3210 a.n. {$platformName}");

        if ($daysDiff > 0) {
            $statusText = "akan jatuh tempo dalam {$daysDiff} hari ke depan (pada tanggal {$formattedDate})";
        } elseif ($daysDiff === 0) {
            $statusText = "jatuh tempo pada hari ini ({$formattedDate})";
        } else {
            $absDays = abs($daysDiff);
            $statusText = "telah melewati jatuh tempo sejak {$absDays} hari yang lalu ({$formattedDate})";
        }

        $defaultTemplateText = "Assalamu'alaikum Wr. Wb. / Salam Sejahtera,\n\n"
            . "Yth. Bpk/Ibu {$sponsor->name},\n\n"
            . "Semoga Bpk/Ibu senantiasa dalam keadaan sehat dan penuh berkah. "
            . "Kami dari pengurus yayasan ingin menginformasikan bahwa komitmen donasi sponsor rutin" . ($sponsor->orphan_name ? " untuk anak asuh tercinta {$sponsor->orphan_name}" : "") . " "
            . "sebesar *{$formattedAmount}* {$statusText}.\n\n"
            . "Pembayaran dapat disalurkan melalui rekening resmi yayasan:\n"
            . "{$paymentInstructions}\n\n"
            . "Setelah melakukan transfer, mohon konfirmasi bukti transfer melalui nomor ini atau email kami.\n\n"
            . "Jazakumullah khairan katsiran atas ketulusan dan kepedulian Bpk/Ibu dalam mendukung masa depan anak-anak asuh kami.\n\n"
            . "Salam hangat,\n"
            . "Pengurus {$platformName}";

        $wavePresets = [];
        foreach ($settings as $s) {
            if (!empty($s->message_template)) {
                $msg = str_replace(
                    ['{sponsor_name}', '{orphan_name}', '{amount}', '{due_date}', '{status_text}', '{wave_label}', '{payment_info}', '{platform_name}'],
                    [$sponsor->name, $sponsor->orphan_name ?? 'anak asuh', $formattedAmount, $formattedDate, $statusText, $s->label, $paymentInstructions, $platformName],
                    $s->message_template
                );
            } else {
                $msg = $defaultTemplateText;
            }
            $wavePresets[(string)$s->id] = [
                'id' => (string)$s->id,
                'label' => $s->label,
                'text' => $msg,
                'channels' => $s->channels ?? [],
            ];
        }
    @endphp

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
                <button 
                    type="button" 
                    @click="$dispatch('open-reminder-modal')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] whitespace-nowrap flex items-center gap-1.5 cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>Send Custom Reminder</span>
                </button>
                <a 
                    href="{{ route('admin.sponsors.edit', $sponsor) }}" 
                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] whitespace-nowrap"
                >
                    Edit Profile
                </a>
            </div>
        </div>
    </x-slot>

    <div 
        x-data="{ 
            reminderModalOpen: false, 
            presets: {{ Js::from($wavePresets) }},
            defaultMsg: {{ Js::from($defaultTemplateText) }},
            selectedWave: '', 
            selectedChannel: '{{ !empty($sponsor->phone) ? 'whatsapp' : (!empty($sponsor->telegram_chat_id) ? 'telegram' : 'email') }}',
            messageText: {{ Js::from($defaultTemplateText) }},
            onWaveChange(val) {
                if (val && this.presets[val]) {
                    this.messageText = this.presets[val].text;
                } else {
                    this.messageText = this.defaultMsg;
                }
            }
        }" 
        @open-reminder-modal.window="reminderModalOpen = true" 
        class="space-y-6"
    >

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs flex items-center gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
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
                            <span class="font-semibold text-slate-800">{{ $sponsor->email ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="text-slate-400 block text-[11px] mb-1">WhatsApp / Phone Number</span>
                            <span class="font-semibold text-slate-800">{{ $sponsor->phone ?? '—' }}</span>
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
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-sm">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.93-1.28 4.88-2.12 5.86-2.54 2.8-.19 3.38 1.15 3.39 1.47z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm">Telegram Bot Activation</h3>
                                <p class="text-[11px] text-slate-400">Automated reminder dispatches via official Telegram Bot</p>
                            </div>
                        </div>

                        @if($sponsor->hasConnectedTelegram())
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Connected
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Not Connected
                            </span>
                        @endif
                    </div>

                    @if($sponsor->hasConnectedTelegram())
                        <div class="p-3.5 bg-emerald-50/60 rounded-xl border border-emerald-100 flex items-start justify-between gap-3 text-xs text-emerald-900">
                            <div>
                                <div class="font-semibold text-emerald-950 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>Account Connected & Active</span>
                                </div>
                                <p class="text-[11px] text-emerald-700 mt-1">
                                    Chat ID: <code class="bg-white/80 px-1.5 py-0.5 rounded font-mono font-bold text-emerald-800">{{ $sponsor->telegram_chat_id }}</code>
                                </p>
                            </div>

                            <form method="POST" action="{{ route('admin.sponsors.disconnect-telegram', $sponsor) }}" onsubmit="return confirm('Are you sure you want to disconnect this sponsor\'s Telegram account?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[11px] text-rose-600 hover:text-rose-800 hover:underline font-medium transition cursor-pointer">
                                    Disconnect Account
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Connect the donor's Telegram account to enable automated reminder dispatches. Share or send an invitation directly:
                        </p>

                        <!-- Quick Send Invitation Actions -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <!-- Send Email Invitation -->
                            <form method="POST" action="{{ route('admin.sponsors.send-telegram-invitation', $sponsor) }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span>Send Invitation via Email</span>
                                </button>
                            </form>

                            <!-- Send WhatsApp Invitation -->
                            @php
                                $waText = rawurlencode("Assalamu'alaikum Wr. Wb. Bpk/Ibu {$sponsor->name},\n\nUntuk mempermudah informasi pengingat donasi rutin dari " . \App\Models\PlatformSetting::getName() . ", silakan hubungkan bot Telegram resmi kami melalui tautan berikut:\n\n{$telegramOnboardUrl}\n\nCukup tekan tombol 'START' pada aplikasi Telegram. Terima kasih!");
                                $waPhone = preg_replace('/[^0-9]/', '', $sponsor->phone ?? '');
                            @endphp
                            @if(!empty($waPhone))
                                <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank" class="flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] cursor-pointer">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                                    <span>Send via WhatsApp</span>
                                </a>
                            @else
                                <button type="button" disabled class="flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-100 text-slate-400 rounded-xl text-xs font-semibold cursor-not-allowed">
                                    <span>No WhatsApp Number</span>
                                </button>
                            @endif
                        </div>

                        <!-- Manual / Direct Activation Link -->
                        <div class="pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[11px] font-semibold text-slate-700">Telegram Onboarding Link:</span>
                                <form method="POST" action="{{ route('admin.sponsors.check-telegram-status', $sponsor) }}">
                                    @csrf
                                    <button type="submit" title="Check if donor has tapped Start in Telegram" class="inline-flex items-center gap-1 text-[11px] font-semibold text-sky-600 hover:text-sky-700 hover:underline cursor-pointer">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Check Status Now</span>
                                    </button>
                                </form>
                            </div>

                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/80 text-[11px] break-all font-mono select-all text-sky-600 flex items-center justify-between gap-2">
                                <span class="truncate">{{ $telegramOnboardUrl }}</span>
                                <a href="{{ $telegramOnboardUrl }}" target="_blank" class="shrink-0 px-2 py-1 bg-sky-100 hover:bg-sky-200 text-sky-800 rounded-md font-sans text-[10px] font-semibold transition">
                                    Open Link ↗
                                </a>
                            </div>

                            <p class="text-[10px] text-slate-400 mt-2">
                                Unique Donor Code: <strong class="text-slate-600 font-mono">{{ $sponsor->telegram_onboard_code }}</strong>
                            </p>
                        </div>
                    @endif
                </div>

            </div>

        </div>

        <!-- 3. Reminder Delivery History -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="font-bold text-slate-900 text-base">Reminder Delivery History</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Audit log of all notification dispatches processed for this donor</p>
                </div>
                <button 
                    type="button" 
                    @click="reminderModalOpen = true" 
                    class="inline-flex items-center gap-1.5 text-xs text-emerald-700 font-semibold hover:text-emerald-800 cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Send Custom Reminder Now</span>
                </button>
            </div>

            <div class="border border-slate-200/70 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 border-b border-slate-200/70 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3">Schedule / Wave</th>
                                <th class="px-5 py-3">Trigger Type</th>
                                <th class="px-5 py-3">Channel</th>
                                <th class="px-5 py-3">Target Due Date</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Sent Timestamp</th>
                                <th class="px-5 py-3">Details / Message</th>
                                <th class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-3 font-semibold text-slate-800 whitespace-nowrap">
                                        {{ $log->reminderSetting->label ?? ($log->customReminder->title ?? ($log->is_manual ? 'Manual Direct' : 'Custom Direct Reminder')) }}
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        @if($log->is_manual)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200/80 whitespace-nowrap">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                Manual
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60 whitespace-nowrap">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Automated
                                            </span>
                                        @endif
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
                                        @if($log->custom_message)
                                            <span class="text-[11px] text-slate-700 block truncate max-w-xs" title="{{ $log->custom_message }}">
                                                {{ Str::limit($log->custom_message, 45) }}
                                            </span>
                                        @elseif($log->error_message)
                                            <span class="text-[11px] text-rose-600 font-mono">{{ $log->error_message }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right whitespace-nowrap">
                                        @if($log->status->value === 'failed')
                                            <form method="POST" action="{{ route('admin.logs.retry', $log) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 active:bg-rose-200 rounded-lg border border-rose-200 transition">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                    Retry
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-300 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-slate-400 text-xs">
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

        <!-- Send Custom Reminder Modal -->
        <div 
            x-show="reminderModalOpen" 
            x-cloak 
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="reminder-modal-title" 
            role="dialog" 
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div 
                x-show="reminderModalOpen" 
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0" 
                x-transition:enter-end="opacity-100" 
                x-transition:leave="ease-in duration-200" 
                x-transition:leave-start="opacity-100" 
                x-transition:leave-end="opacity-0" 
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                @click="reminderModalOpen = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div 
                    x-show="reminderModalOpen" 
                    x-transition:enter="ease-out duration-300" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave="ease-in duration-200" 
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl p-6 space-y-5"
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="reminder-modal-title">Send Custom Reminder</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Directly dispatch a personalized reminder to {{ $sponsor->name }}</p>
                        </div>
                        <button 
                            type="button" 
                            @click="reminderModalOpen = false"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Sponsor Summary Strip -->
                    <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl text-xs flex items-center justify-between">
                        <div>
                            <span class="text-[11px] text-slate-400 block">Sponsor & Amount</span>
                            <span class="font-bold text-slate-800">{{ $sponsor->name }} ({{ $sponsor->formatted_amount }})</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400 block">Next Due Date</span>
                            <span class="font-bold text-slate-800">{{ $nextDue->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.sponsors.send-reminder', $sponsor) }}" class="space-y-4">
                        @csrf

                        <!-- Preset Template Selector -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5 text-xs">Message Template Preset</label>
                            <select 
                                x-model="selectedWave" 
                                @change="onWaveChange($event.target.value)"
                                name="reminder_setting_id"
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400"
                            >
                                <option value="">Platform Default Reminder Template</option>
                                @foreach($settings as $s)
                                    <option value="{{ $s->id }}">{{ $s->label }} (Target: {{ $s->days_before_due > 0 ? 'H-' . $s->days_before_due : ($s->days_before_due === 0 ? 'Due Date' : 'Overdue +' . abs($s->days_before_due) . 'd') }})</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1">Select a wave template to auto-populate the message text below with resolved sponsor tags.</p>
                        </div>

                        <!-- Delivery Channel -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5 text-xs">Delivery Channel <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                <!-- WhatsApp -->
                                <label class="flex flex-col p-2.5 rounded-xl border cursor-pointer transition text-xs {{ !empty($sponsor->phone) ? 'border-slate-200/80 hover:bg-slate-50' : 'opacity-50 border-slate-200 cursor-not-allowed bg-slate-50' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <input type="radio" name="channel" value="whatsapp" x-model="selectedChannel" {{ empty($sponsor->phone) ? 'disabled' : '' }} class="text-slate-900 focus:ring-slate-500">
                                            <span class="ms-2 font-semibold text-slate-800">WhatsApp</span>
                                        </div>
                                        <span class="w-2 h-2 rounded-full {{ !empty($sponsor->phone) ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-1 truncate">{{ $sponsor->phone ?: 'No phone number' }}</span>
                                </label>

                                <!-- Telegram -->
                                <label class="flex flex-col p-2.5 rounded-xl border cursor-pointer transition text-xs {{ !empty($sponsor->telegram_chat_id) ? 'border-slate-200/80 hover:bg-slate-50' : 'opacity-60 border-slate-200 bg-slate-50' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <input type="radio" name="channel" value="telegram" x-model="selectedChannel" {{ empty($sponsor->telegram_chat_id) ? 'disabled' : '' }} class="text-slate-900 focus:ring-slate-500">
                                            <span class="ms-2 font-semibold text-slate-800">Telegram</span>
                                        </div>
                                        <span class="w-2 h-2 rounded-full {{ !empty($sponsor->telegram_chat_id) ? 'bg-emerald-500' : 'bg-amber-400' }}"></span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-1 truncate">{{ !empty($sponsor->telegram_chat_id) ? 'Connected' : 'Not Connected' }}</span>
                                </label>

                                <!-- Email -->
                                <label class="flex flex-col p-2.5 rounded-xl border cursor-pointer transition text-xs {{ !empty($sponsor->email) ? 'border-slate-200/80 hover:bg-slate-50' : 'opacity-50 border-slate-200 cursor-not-allowed bg-slate-50' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <input type="radio" name="channel" value="email" x-model="selectedChannel" {{ empty($sponsor->email) ? 'disabled' : '' }} class="text-slate-900 focus:ring-slate-500">
                                            <span class="ms-2 font-semibold text-slate-800">Email</span>
                                        </div>
                                        <span class="w-2 h-2 rounded-full {{ !empty($sponsor->email) ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-1 truncate">{{ $sponsor->email ?: 'No email' }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Editable Message Body -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block font-semibold text-slate-700 text-xs">
                                    Message Text <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[10px] text-slate-400" x-text="messageText.length + ' characters'"></span>
                            </div>

                            <textarea 
                                name="message" 
                                rows="7" 
                                x-model="messageText" 
                                required 
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 font-mono text-[11px] leading-relaxed focus:border-slate-400 focus:ring-slate-400"
                            ></textarea>
                            <p class="text-[10px] text-slate-400 mt-1">You can customize or tailor this message before sending.</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                            <button 
                                type="button" 
                                @click="reminderModalOpen = false"
                                class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] flex items-center gap-1.5 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>Send Reminder Now</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
