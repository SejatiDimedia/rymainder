<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
                    Log Audit Pengiriman Reminder
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Audit trail terpusat seluruh pengiriman notifikasi via Email, WhatsApp, dan Telegram.
                </p>
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
                <form method="GET" action="{{ route('admin.logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Cari Sponsor</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / email / no HP..." class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Status Pengiriman</label>
                        <select name="status" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $st)
                                <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                                    {{ $st->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Channel Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Channel Notifikasi</label>
                        <select name="channel" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                            <option value="">Semua Channel</option>
                            @foreach($channels as $ch)
                                <option value="{{ $ch->value }}" {{ request('channel') === $ch->value ? 'selected' : '' }}>
                                    {{ $ch->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Target Jatuh Tempo</label>
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                    </div>

                    <!-- Actions -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full px-3 py-2 bg-gray-900 hover:bg-gray-800 text-white dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg text-xs font-medium transition">
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'status', 'channel', 'date']))
                            <a href="{{ route('admin.logs.index') }}" class="px-3 py-2 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-xs transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 uppercase font-semibold text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Waktu</th>
                                <th class="px-5 py-3">Sponsor & Kontak</th>
                                <th class="px-5 py-3">Gelombang / Wave</th>
                                <th class="px-5 py-3">Channel</th>
                                <th class="px-5 py-3">Target Jatuh Tempo</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Detail Respon / Error</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30">
                                    <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                                        {{ $log->created_at->translatedFormat('d M Y, H:i:s') }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="font-bold text-gray-900 dark:text-white">
                                            @if($log->sponsor)
                                                <a href="{{ route('admin.sponsors.show', $log->sponsor) }}" class="hover:underline text-teal-600 dark:text-teal-400">
                                                    {{ $log->sponsor->name }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">[Dihapus]</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-400">
                                            {{ $log->channel->value === 'email' ? ($log->sponsor->email ?? '') : ($log->sponsor->phone ?? '') }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-gray-800 dark:text-gray-200">
                                        {{ $log->reminderSetting->label ?? 'Custom Wave' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded {{ $log->channel->badgeClasses() }}">
                                            {{ $log->channel->label() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 font-mono text-gray-700 dark:text-gray-300">
                                        {{ $log->due_date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded {{ $log->status->badgeClasses() }}">
                                            {{ $log->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 max-w-xs truncate text-gray-600 dark:text-gray-300" title="{{ $log->error_message }}">
                                        {{ $log->error_message ?? ($log->status->value === 'sent' ? 'Pesan berhasil diterima provider' : '—') }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        @if($log->status->value === 'failed')
                                            <form method="POST" action="{{ route('admin.logs.retry', $log) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-100 rounded border border-rose-200 dark:border-rose-800 transition">
                                                    Kirim Ulang
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                        Belum ada catatan log pengiriman yang cocok.
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
