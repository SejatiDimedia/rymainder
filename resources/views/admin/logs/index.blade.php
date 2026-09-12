<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Reminder Delivery Logs</h1>
                <p class="text-xs text-slate-500 mt-0.5">Centralized audit trail of all notifications dispatched across Email, WhatsApp, and Telegram</p>
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

        <!-- Filter & Search Bar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('admin.logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                <!-- Search Input -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Search Sponsor</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Name / email / phone..." 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:ring-slate-400"
                    >
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Delivery Status</label>
                    <select 
                        name="status" 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400"
                    >
                        <option value="">All Statuses</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Channel Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Notification Channel</label>
                    <select 
                        name="channel" 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400"
                    >
                        <option value="">All Channels</option>
                        @foreach($channels as $ch)
                            <option value="{{ $ch->value }}" {{ request('channel') === $ch->value ? 'selected' : '' }}>
                                {{ $ch->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Target Due Date</label>
                    <input 
                        type="date" 
                        name="date" 
                        value="{{ request('date') }}" 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400"
                    >
                </div>

                <!-- Actions -->
                <div class="flex items-end gap-2">
                    <button 
                        type="submit" 
                        class="w-full px-3 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition whitespace-nowrap"
                    >
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'channel', 'date']))
                        <a 
                            href="{{ route('admin.logs.index') }}" 
                            class="px-3 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-medium transition whitespace-nowrap"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Audit Logs Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Dispatch Activity History</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Comprehensive execution logs with external provider responses and error messages</p>
                </div>
                <div class="text-xs text-slate-500 whitespace-nowrap">
                    Total: <span class="font-semibold text-slate-800">{{ $logs->total() }}</span> logs
                </div>
            </div>

            <!-- Inset Table -->
            <div class="border border-slate-200/70 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 border-b border-slate-200/70 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3.5">Dispatch Time</th>
                                <th class="px-5 py-3.5">Sponsor & Contact</th>
                                <th class="px-5 py-3.5">Schedule / Wave</th>
                                <th class="px-5 py-3.5">Channel</th>
                                <th class="px-5 py-3.5">Target Due</th>
                                <th class="px-5 py-3.5">Status</th>
                                <th class="px-5 py-3.5">Response Details</th>
                                <th class="px-5 py-3.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap">
                                        {{ $log->created_at->translatedFormat('d M Y, H:i:s') }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-slate-900 text-xs">
                                            @if($log->sponsor)
                                                <a href="{{ route('admin.sponsors.show', $log->sponsor) }}" class="hover:text-blue-600 transition">
                                                    {{ $log->sponsor->name }}
                                                </a>
                                            @else
                                                <span class="text-slate-400">[Deleted]</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $log->channel->value === 'email' ? ($log->sponsor->email ?? '') : ($log->sponsor->phone ?? '') }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-700 font-medium whitespace-nowrap">
                                        {{ $log->reminderSetting->label ?? 'Custom Wave' }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] whitespace-nowrap {{ $log->channel->badgeClasses() }}">
                                            {{ $log->channel->label() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 font-medium text-slate-700 whitespace-nowrap">
                                        {{ $log->due_date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] whitespace-nowrap {{ $log->status->badgeClasses() }}">
                                            {{ $log->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 max-w-xs truncate text-slate-600 text-[11px]" title="{{ $log->error_message }}">
                                        {{ $log->error_message ?? ($log->status->value === 'sent' ? 'Message successfully received by provider' : '—') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        @if($log->status->value === 'failed')
                                            <form method="POST" action="{{ route('admin.logs.retry', $log) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-200 transition">
                                                    Retry Send
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-300 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-400 text-xs">
                                        No delivery logs matching the current filter criteria.
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
