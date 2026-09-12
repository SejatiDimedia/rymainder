<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-bold text-2xl text-gray-900 dark:text-gray-100 leading-tight">
                        {{ $sponsor->name }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sponsor->status->badgeClasses() }}">
                        {{ $sponsor->status->label() }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Profil donatur, aktivasi Telegram Bot, dan riwayat pengiriman reminder.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.sponsors.index') }}" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    ← Daftar Sponsor
                </a>
                <a href="{{ route('admin.sponsors.edit', $sponsor) }}" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm font-medium transition shadow-sm">
                    Edit Profil
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- 1. Detail Profil & Jadwal (2 Cols) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-100 dark:border-gray-700">
                            Informasi Donasi & Siklus
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block text-xs">Email</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $sponsor->email }}</span>
                            </div>

                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block text-xs">WhatsApp / HP</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $sponsor->phone }}</span>
                            </div>

                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block text-xs">Anak Asuh</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $sponsor->orphan_name ?? 'Donasi Umum (Tidak Ada)' }}</span>
                            </div>

                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block text-xs">Nominal Komitmen</span>
                                <span class="font-bold text-teal-600 dark:text-teal-400 text-base">{{ $sponsor->formatted_amount }}</span>
                            </div>

                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block text-xs">Frekuensi Siklus</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $sponsor->frequency->label() }}</span>
                            </div>

                            <div>
                                <span class="text-gray-500 dark:text-gray-400 block text-xs">Tanggal Donasi Terakhir</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $sponsor->last_donation_date->translatedFormat('d F Y') }}</span>
                            </div>
                        </div>

                        <!-- Channel Preferences -->
                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <span class="text-gray-500 dark:text-gray-400 block text-xs mb-2">Preferensi Channel Notifikasi Sponsor</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['email' => 'Email', 'whatsapp' => 'WhatsApp', 'telegram' => 'Telegram'] as $key => $label)
                                    @php
                                        $isEnabled = $sponsor->isChannelEnabled(\App\Domain\Reminder\Enums\ReminderChannel::from($key));
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium {{ $isEnabled ? 'bg-teal-50 text-teal-700 border border-teal-200 dark:bg-teal-900/30 dark:text-teal-300' : 'bg-gray-100 text-gray-400 border border-gray-200 dark:bg-gray-800' }}">
                                        {{ $label }}: {{ $isEnabled ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        @if($sponsor->notes)
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500 dark:text-gray-400 block text-xs mb-1">Catatan Staf</span>
                                <p class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/40 p-3 rounded-lg">{{ $sponsor->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 2. Status Jatuh Tempo & Telegram Onboarding (1 Col) -->
                <div class="space-y-6">

                    <!-- Due Date Countdown Box -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1">Status Jatuh Tempo</span>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $nextDue->translatedFormat('d F Y') }}
                        </div>

                        <div class="mt-3">
                            @if($daysDiff < 0)
                                <div class="p-3 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 rounded-lg text-rose-800 dark:text-rose-300 text-xs font-medium">
                                    ⚠️ Telat <strong>{{ abs($daysDiff) }} hari</strong> dari tanggal jatuh tempo.
                                </div>
                            @elseif($daysDiff === 0)
                                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-lg text-amber-800 dark:text-amber-300 text-xs font-medium">
                                    ⏰ Jatuh tempo <strong>HARI INI</strong>.
                                </div>
                            @elseif($daysDiff <= 7)
                                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-lg text-amber-800 dark:text-amber-300 text-xs font-medium">
                                    ⏳ Jatuh tempo dalam <strong>{{ $daysDiff }} hari ke depan</strong>.
                                </div>
                            @else
                                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-lg text-emerald-800 dark:text-emerald-300 text-xs font-medium">
                                    ✓ Jadwal donasi masih <strong>{{ $daysDiff }} hari lagi</strong>.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Telegram Onboarding Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-bold text-gray-900 dark:text-white text-sm">Aktivasi Bot Telegram</h4>
                            @if($sponsor->hasConnectedTelegram())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    Terhubung
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                    Belum Terhubung
                                </span>
                            @endif
                        </div>

                        @if($sponsor->hasConnectedTelegram())
                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                Akun Telegram telah aktif dengan Chat ID: <code class="bg-gray-100 dark:bg-gray-900 px-1 py-0.5 rounded">{{ $sponsor->telegram_chat_id }}</code>. Sponsor akan menerima pesan otomatis di bot yayasan.
                            </p>
                        @else
                            <p class="text-xs text-gray-600 dark:text-gray-300 mb-3">
                                Bagikan tautan aktivasi berikut kepada sponsor agar akun Telegram terhubung secara otomatis:
                            </p>

                            <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg border border-gray-200 dark:border-gray-700 text-xs break-all font-mono select-all text-teal-700 dark:text-teal-400">
                                {{ $telegramOnboardUrl }}
                            </div>

                            <p class="text-[11px] text-gray-400 mt-2">
                                Kode Unik: <strong>{{ $sponsor->telegram_onboard_code }}</strong>
                            </p>
                        @endif
                    </div>

                </div>

            </div>

            <!-- 3. Riwayat Reminder (Audit Trail) -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-base">Riwayat Pengiriman Reminder</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Semua riwayat pengiriman notifikasi ke sponsor ini beserta status dan pesan error.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Gelombang / Wave</th>
                                <th class="px-6 py-3">Channel</th>
                                <th class="px-6 py-3">Target Jatuh Tempo</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Waktu Terkirim</th>
                                <th class="px-6 py-3">Keterangan / Error</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ $log->reminderSetting->label ?? 'Custom Wave' }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded {{ $log->channel->badgeClasses() }}">
                                            {{ $log->channel->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                        {{ $log->due_date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded {{ $log->status->badgeClasses() }}">
                                            {{ $log->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-500">
                                        {{ $log->sent_at ? $log->sent_at->translatedFormat('d M Y, H:i') : '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-500">
                                        {{ $log->error_message ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                        Belum ada riwayat reminder yang tercatat untuk sponsor ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
