<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
                    Data Sponsor & Donatur
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Kelola data donatur, jadwal siklus donasi, dan status channel notifikasi.
                </p>
            </div>
            <div>
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

            <!-- Filter & Search Bar -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <form method="GET" action="{{ route('admin.sponsors.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Cari Sponsor / Anak Asuh</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama, email, no HP..." class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Status Sponsor</label>
                        <select name="status" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Frequency Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Frekuensi Donasi</label>
                        <select name="frequency" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                            <option value="">Semua Frekuensi</option>
                            @foreach($frequencies as $freq)
                                <option value="{{ $freq->value }}" {{ request('frequency') === $freq->value ? 'selected' : '' }}>
                                    {{ $freq->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg text-sm font-medium transition">
                            Terapkan Filter
                        </button>
                        @if(request()->hasAny(['search', 'status', 'frequency']))
                            <a href="{{ route('admin.sponsors.index') }}" class="px-3 py-2 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-sm transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Sponsor Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3.5">Indikator</th>
                                <th class="px-5 py-3.5">Nama Sponsor & Kontak</th>
                                <th class="px-5 py-3.5">Anak Asuh & Nominal</th>
                                <th class="px-5 py-3.5">Siklus & Donasi Terakhir</th>
                                <th class="px-5 py-3.5">Jatuh Tempo Berikutnya</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($sponsors as $sponsor)
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30 transition">
                                    <!-- Status Indicator Dot -->
                                    <td class="px-5 py-4">
                                        @if($sponsor->due_indicator === 'red')
                                            <span class="inline-block w-3.5 h-3.5 rounded-full bg-rose-500 shadow-sm" title="Terlambat (Overdue)"></span>
                                        @elseif($sponsor->due_indicator === 'yellow')
                                            <span class="inline-block w-3.5 h-3.5 rounded-full bg-amber-400 shadow-sm" title="Jatuh tempo dalam ≤ 7 hari"></span>
                                        @else
                                            <span class="inline-block w-3.5 h-3.5 rounded-full bg-emerald-500 shadow-sm" title="Jadwal masih aman (> 7 hari)"></span>
                                        @endif
                                    </td>

                                    <!-- Sponsor Info -->
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">
                                            <a href="{{ route('admin.sponsors.show', $sponsor) }}" class="hover:text-teal-600 dark:hover:text-teal-400">
                                                {{ $sponsor->name }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ $sponsor->email }} • {{ $sponsor->phone }}
                                        </div>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            @if($sponsor->hasConnectedTelegram())
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300">
                                                    Telegram Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                    Belum Telegram
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Orphan & Amount -->
                                    <td class="px-5 py-4">
                                        <div class="font-medium text-gray-800 dark:text-gray-200">
                                            {{ $sponsor->orphan_name ?? '—' }}
                                        </div>
                                        <div class="text-xs font-semibold text-teal-600 dark:text-teal-400 mt-0.5">
                                            {{ $sponsor->formatted_amount }}
                                        </div>
                                    </td>

                                    <!-- Frequency & Last Donation -->
                                    <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                        <div>{{ $sponsor->frequency->label() }}</div>
                                        <div class="text-gray-400 mt-0.5">Terakhir: {{ $sponsor->last_donation_date->translatedFormat('d M Y') }}</div>
                                    </td>

                                    <!-- Next Due Date -->
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900 dark:text-white text-xs">
                                            {{ $sponsor->computed_next_due->translatedFormat('d F Y') }}
                                        </div>
                                        <div class="mt-0.5">
                                            @if($sponsor->days_diff < 0)
                                                <span class="text-xs font-medium text-rose-600 dark:text-rose-400">
                                                    Telat {{ abs($sponsor->days_diff) }} hari
                                                </span>
                                            @elseif($sponsor->days_diff === 0)
                                                <span class="text-xs font-medium text-amber-600 dark:text-amber-400">
                                                    Jatuh tempo HARI INI
                                                </span>
                                            @elseif($sponsor->days_diff <= 7)
                                                <span class="text-xs font-medium text-amber-600 dark:text-amber-400">
                                                    {{ $sponsor->days_diff }} hari lagi
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $sponsor->days_diff }} hari lagi
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $sponsor->status->badgeClasses() }}">
                                            {{ $sponsor->status->label() }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-5 py-4 text-right space-x-1">
                                        <a href="{{ route('admin.sponsors.show', $sponsor) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            Lihat
                                        </a>
                                        <a href="{{ route('admin.sponsors.edit', $sponsor) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                        Tidak ada data sponsor yang cocok dengan pencarian / filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($sponsors->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $sponsors->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
