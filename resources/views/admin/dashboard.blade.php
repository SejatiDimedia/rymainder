<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
                    Dashboard Monitoring Reminder
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Ringkasan otomatis status jatuh tempo donasi dan audit pengiriman reminder harian.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.sponsors.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Sponsor Baru
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

            <!-- 1. KPI Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total Active -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sponsor Aktif</span>
                        <span class="p-2 bg-teal-50 dark:bg-teal-900/40 text-teal-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalActiveSponsors }}</span>
                        <span class="text-xs text-gray-500">({{ $totalPausedSponsors }} ditunda)</span>
                    </div>
                    <div class="mt-2 text-xs text-teal-700 dark:text-teal-400 font-medium">Dalam pemantauan scheduler</div>
                </div>

                <!-- Due Soon (<= 7 Days) -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Jatuh Tempo (≤ 7 Hari)</span>
                        <span class="p-2 bg-amber-50 dark:bg-amber-900/40 text-amber-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ $dueSoonCount }}</span>
                        <span class="text-xs text-gray-500">sponsor</span>
                    </div>
                    <div class="mt-2 text-xs text-amber-600 dark:text-amber-400 font-medium">Masuk antrean Wave H-7 / H-3</div>
                </div>

                <!-- Overdue (Telat) -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-400">Terlambat (Overdue)</span>
                        <span class="p-2 bg-rose-50 dark:bg-rose-900/40 text-rose-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ $overdueCount }}</span>
                        <span class="text-xs text-gray-500">sponsor</span>
                    </div>
                    <div class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-medium">Perlu follow-up keterlambatan</div>
                </div>

                <!-- Today Sent Logs -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Reminder Hari Ini</span>
                        <span class="p-2 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-emerald-600">{{ $todaySentCount }}</span>
                        <span class="text-xs text-gray-500">berhasil</span>
                    </div>
                    <div class="mt-2 text-xs text-gray-500 flex items-center gap-2">
                        <span>{{ $todayFailedCount }} gagal</span> • 
                        <span>{{ $todaySkippedCount }} dilewati</span>
                    </div>
                </div>
            </div>

            <!-- 2. Priority Sponsors Table & Recent Logs -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Priority Sponsors List (2 Cols) -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white text-base">Prioritas Jatuh Tempo Terdekat</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Daftar sponsor yang memerlukan perhatian atau pengingat segera.</p>
                        </div>
                        <a href="{{ route('admin.sponsors.index') }}" class="text-xs font-semibold text-teal-600 hover:text-teal-700">
                            Lihat Semua →
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Sponsor & Anak Asuh</th>
                                    <th class="px-6 py-3">Tanggal Jatuh Tempo</th>
                                    <th class="px-6 py-3">Status Tempo</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($prioritySponsors as $item)
                                    @php
                                        $s = $item['sponsor'];
                                        $diff = $item['days_diff'];
                                        $type = $item['status_type'];
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition">
                                        <td class="px-6 py-3.5">
                                            <div class="font-semibold text-gray-900 dark:text-white">{{ $s->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $s->orphan_name ? 'Anak asuh: ' . $s->orphan_name : 'Program Umum' }} • {{ $s->formatted_amount }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-3.5 text-gray-700 dark:text-gray-300">
                                            {{ $item['next_due']->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="px-6 py-3.5">
                                            @if($type === 'overdue')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border border-rose-200">
                                                    Telat {{ abs($diff) }} hari
                                                </span>
                                            @elseif($type === 'due_soon')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200">
                                                    {{ $diff === 0 ? 'Hari ini' : $diff . ' hari lagi' }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200">
                                                    {{ $diff }} hari lagi
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-right">
                                            <a href="{{ route('admin.sponsors.show', $s) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                            Belum ada data sponsor aktif.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activity Logs (1 Col) -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="font-bold text-gray-900 dark:text-white text-base">Aktivitas Reminder Terbaru</h3>
                            <a href="{{ route('admin.logs.index') }}" class="text-xs font-semibold text-teal-600 hover:text-teal-700">
                                Log Lengkap →
                            </a>
                        </div>

                        <div class="space-y-3">
                            @forelse($recentLogs as $log)
                                <div class="p-3 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-gray-900 dark:text-gray-100 truncate max-w-[150px]">
                                            {{ $log->sponsor->name ?? 'Sponsor' }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $log->status->badgeClasses() }}">
                                            {{ $log->status->label() }}
                                        </span>
                                    </div>
                                    <div class="mt-1 text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] {{ $log->channel->badgeClasses() }}">
                                            {{ $log->channel->label() }}
                                        </span>
                                        <span>•</span>
                                        <span>{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if($log->error_message)
                                        <div class="mt-1 text-rose-600 dark:text-rose-400 text-[11px] truncate">
                                            {{ $log->error_message }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-6 text-gray-400 text-xs">
                                    Belum ada catatan log reminder.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500">
                        <div class="flex items-center justify-between">
                            <span>Wave Aktif: <strong>{{ $activeWavesCount }} Gelombang</strong></span>
                            <span>Scheduler: <strong>08:00 WIB</strong></span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
