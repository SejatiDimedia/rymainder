<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Dashboard Monitoring Reminder</h1>
            <p class="text-xs text-slate-500 mt-0.5">Ringkasan status jatuh tempo donasi dan audit pengiriman reminder harian</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- 1. Top Sub-Navigation Tabs matching reference (Billing | Payment) -->
        <div class="flex items-center gap-1 p-1 bg-slate-200/50 rounded-xl w-fit text-xs">
            <span class="px-4 py-1.5 rounded-lg bg-white font-semibold text-slate-900 shadow-xs cursor-default">
                Ringkasan Utama
            </span>
            <a href="{{ route('admin.sponsors.index') }}" class="px-4 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition">
                Data Sponsor
            </a>
            <a href="{{ route('admin.logs.index') }}" class="px-4 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition">
                Audit Log
            </a>
        </div>

        <!-- 2. Alert Notification Banner matching reference -->
        <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="text-xs text-indigo-950">
                    <span class="font-semibold">Scheduler otomatis aktif:</span> Pengiriman reminder berjalan harian setiap pukul <strong>08:00 WIB</strong> (Waktu Server: {{ now()->format('H:i:s T') }}).
                </div>
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.sponsors.index') }}">
                    @csrf
                    <a 
                        href="{{ route('admin.sponsors.index') }}" 
                        class="inline-flex items-center justify-center px-4 py-2 bg-white hover:bg-indigo-50 border border-indigo-200/80 rounded-xl text-xs font-semibold text-indigo-900 shadow-xs hover:border-indigo-300 transition active:scale-[0.98]"
                    >
                        Lihat Antrean Aktif
                    </a>
                </form>
            </div>
        </div>

        <!-- 3. Dual Hero KPI Cards matching reference (Your upcoming bills & Billings Info) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Left Card: Your upcoming bills / Siklus Reminder & Donasi -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <!-- Card Top Bar -->
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="font-bold text-slate-900 text-sm">Komitmen Donasi Aktif</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Siklus tahunan & 6 bulanan terverifikasi</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a 
                                href="{{ route('admin.sponsors.create') }}" 
                                class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200/90 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 shadow-xs transition"
                            >
                                + Tambah
                            </a>
                            <a 
                                href="{{ route('admin.sponsors.index') }}" 
                                class="w-8 h-8 rounded-xl border border-slate-200/90 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition"
                                title="Menu Sponsor"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="19" cy="12" r="1"></circle>
                                    <circle cx="5" cy="12" r="1"></circle>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Big Metric Amount Display matching $70.00 USD -->
                    <div class="mt-5 mb-6 flex items-baseline gap-2">
                        <span class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">
                            Rp {{ number_format($totalActiveAmount ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-xs font-medium text-slate-400">IDR / Siklus</span>
                    </div>
                </div>

                <!-- Bottom Details Bar inside card -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>{{ $dueSoonCount }} Sponsor jatuh tempo ≤ 7 hari</span>
                    </div>

                    <a 
                        href="{{ route('admin.sponsors.index') }}" 
                        class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98]"
                    >
                        Kelola
                    </a>
                </div>
            </div>

            <!-- Right Card: Billings Info / Channel Gateway Status -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <!-- Card Top Bar -->
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="font-bold text-slate-900 text-sm">Status Channel Notifikasi</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Integrasi gateway pengiriman pengingat</p>
                        </div>
                        <a 
                            href="{{ route('admin.settings.index') }}" 
                            class="w-8 h-8 rounded-xl border border-slate-200/90 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition"
                            title="Pengaturan Gelombang"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="1"></circle>
                                <circle cx="19" cy="12" r="1"></circle>
                                <circle cx="5" cy="12" r="1"></circle>
                            </svg>
                        </a>
                    </div>

                    <!-- Visual Gateway Badge matching the dark VISA badge in reference -->
                    <div class="mt-4 mb-4">
                        <div class="inline-flex items-center gap-3 px-3.5 py-2 rounded-xl bg-slate-900 text-white shadow-xs">
                            <span class="text-[10px] font-bold tracking-widest uppercase bg-slate-800 px-2 py-0.5 rounded text-teal-400">
                                MULTI-CHANNEL
                            </span>
                            <div class="flex items-center gap-2 text-xs text-slate-300">
                                <span>WA Cloud</span>
                                <span>•</span>
                                <span>Telegram</span>
                                <span>•</span>
                                <span>Email</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Details Bar inside card -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-semibold text-slate-800">
                            {{ $activeWavesCount }} Gelombang Pengingat Aktif
                        </div>
                        <div class="text-[11px] text-slate-500">
                            Hari ini: {{ $todaySentCount }} terkirim, {{ $todayFailedCount }} gagal
                        </div>
                    </div>

                    @if(Auth::user()->isSuperAdmin())
                        <a 
                            href="{{ route('admin.settings.index') }}" 
                            class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-200/90 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 shadow-xs transition"
                        >
                            Edit Gelombang
                        </a>
                    @else
                        <span class="text-xs text-slate-400">Terkonfigurasi</span>
                    @endif
                </div>
            </div>

        </div>

        <!-- 4. Main Inset Table Card matching "Invoices" in reference -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs space-y-4">
            <!-- Header section of the table card -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-base">Prioritas Jatuh Tempo Terdekat</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar sponsor yang dijadwalkan menerima reminder dalam waktu dekat</p>
                </div>
                <a href="{{ route('admin.sponsors.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition">
                    Lihat Semua Sponsor →
                </a>
            </div>

            <!-- Inset Table with subtle rounded frame -->
            <div class="border border-slate-200/70 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 border-b border-slate-200/70 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3">Sponsor & Anak Asuh</th>
                                <th class="px-5 py-3">Frekuensi</th>
                                <th class="px-5 py-3">Nominal Donasi</th>
                                <th class="px-5 py-3">Tanggal Jatuh Tempo</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($prioritySponsors as $item)
                                @php
                                    $s = $item['sponsor'];
                                    $diff = $item['days_diff'];
                                    $type = $item['status_type'];
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <!-- Sponsor & Anak Asuh -->
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-slate-900 text-xs">{{ $s->name }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            {{ $s->orphan_name ? 'Anak asuh: ' . $s->orphan_name : 'Program Reguler' }}
                                        </div>
                                    </td>

                                    <!-- Frekuensi -->
                                    <td class="px-5 py-3.5 text-slate-600">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium">
                                            {{ $s->frequency->label() }}
                                        </span>
                                    </td>

                                    <!-- Amount matching "- $70" in reference -->
                                    <td class="px-5 py-3.5 font-medium text-rose-600">
                                        - {{ $s->formatted_amount }}
                                    </td>

                                    <!-- Billing Date matching "Mar 15, 2025 • 09.41 PM" in reference -->
                                    <td class="px-5 py-3.5 text-slate-600">
                                        {{ $item['next_due']->translatedFormat('d M Y') }} • 08:00 WIB
                                    </td>

                                    <!-- Status with leading bullet dot matching "● Paid" in reference -->
                                    <td class="px-5 py-3.5">
                                        @if($type === 'overdue')
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-rose-600 text-xs">
                                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                                Telat {{ abs($diff) }} hari
                                            </span>
                                        @elseif($type === 'due_soon')
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-amber-600 text-xs">
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                {{ $diff === 0 ? 'Hari ini' : $diff . ' hari lagi' }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-600 text-xs">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                {{ $diff }} hari lagi
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-5 py-3.5 text-right">
                                        <a 
                                            href="{{ route('admin.sponsors.show', $s) }}" 
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 transition shadow-2xs"
                                        >
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-slate-400 text-xs">
                                        Belum ada data sponsor aktif dalam antrean.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table Footer / Pagination matching reference (Show data 2 of 2, pill 1, Next ->) -->
            <div class="flex items-center justify-between pt-2 text-xs text-slate-500">
                <div>
                    Show data <span class="font-semibold text-slate-800">{{ count($prioritySponsors) }}</span> of <span class="font-semibold text-slate-800">{{ $totalActiveSponsors }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-600 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                        1
                    </span>
                    <a href="{{ route('admin.sponsors.index') }}" class="text-slate-600 hover:text-slate-900 font-medium transition flex items-center gap-1">
                        Next →
                    </a>
                </div>
            </div>
        </div>

        <!-- 5. Recent Activity Logs Section (Compact) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Aktivitas Reminder Terbaru</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Audit pengiriman notifikasi terakhir oleh worker scheduler</p>
                </div>
                <a href="{{ route('admin.logs.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition">
                    Log Lengkap →
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @forelse($recentLogs as $log)
                    <div class="p-3.5 rounded-xl border border-slate-200/70 bg-slate-50/50 text-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-800 truncate max-w-[130px]">
                                    {{ $log->sponsor->name ?? 'Sponsor' }}
                                </span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $log->status->badgeClasses() }}">
                                    {{ $log->status->label() }}
                                </span>
                            </div>
                            <div class="mt-1.5 text-slate-500 text-[11px] flex items-center gap-1.5">
                                <span class="font-medium text-slate-700">{{ $log->channel->label() }}</span>
                                <span>•</span>
                                <span>{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        @if($log->error_message)
                            <div class="mt-2 text-rose-600 text-[10px] font-mono truncate" title="{{ $log->error_message }}">
                                {{ $log->error_message }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-4 text-center py-6 text-slate-400 text-xs">
                        Belum ada catatan aktivitas reminder pengiriman.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
