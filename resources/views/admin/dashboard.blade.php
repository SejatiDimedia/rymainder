<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Dashboard Monitoring Reminder</h1>
            <p class="text-xs text-slate-500 mt-0.5">Automated donation due dates overview & multi-channel reminder audits</p>
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

        <!-- 1. Top Sub-Navigation Tabs (Overview | Sponsors | Delivery Logs) -->
        <div class="flex items-center gap-1 p-1 bg-slate-200/50 rounded-xl w-fit text-xs">
            <span class="px-4 py-1.5 rounded-lg bg-white font-semibold text-slate-900 shadow-xs cursor-default">
                Overview
            </span>
            <a href="{{ route('admin.sponsors.index') }}" class="px-4 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition">
                Sponsors
            </a>
            <a href="{{ route('admin.logs.index') }}" class="px-4 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition">
                Delivery Logs
            </a>
        </div>

        <!-- 2. Alert Notification Banner: Strictly 1 Line Text -->
        <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3 min-w-0 flex-1 overflow-hidden">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <!-- Strictly 1 line truncate -->
                <div class="text-xs text-indigo-950 truncate whitespace-nowrap">
                    <span class="font-semibold">Automated scheduler active:</span> Daily reminder queue executes at <strong>08:00 WIB</strong> (Server Time: {{ now()->format('H:i T') }}).
                </div>
            </div>

            <div class="shrink-0">
                <a 
                    href="{{ route('admin.sponsors.index') }}" 
                    class="inline-flex items-center justify-center px-4 py-2 bg-white hover:bg-indigo-50 border border-indigo-200/80 rounded-xl text-xs font-semibold text-indigo-900 shadow-xs hover:border-indigo-300 transition active:scale-[0.98] whitespace-nowrap"
                >
                    View Active Queue
                </a>
            </div>
        </div>

        <!-- 3. Dual Hero KPI Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Left Card: Active Donation Commitments -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <!-- Card Top Bar -->
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="font-bold text-slate-900 text-sm">Active Donation Commitments</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Verified annual & 6-month cycles</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a 
                                href="{{ route('admin.sponsors.create') }}" 
                                class="inline-flex items-center px-3 py-1.5 rounded-xl border border-slate-200/90 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 shadow-xs transition"
                            >
                                + Add
                            </a>
                            <a 
                                href="{{ route('admin.sponsors.index') }}" 
                                class="w-8 h-8 rounded-xl border border-slate-200/90 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition"
                                title="Manage Sponsors"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="19" cy="12" r="1"></circle>
                                    <circle cx="5" cy="12" r="1"></circle>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Big Metric Amount Display -->
                    <div class="mt-5 mb-6 flex items-baseline gap-2">
                        <span class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">
                            Rp {{ number_format($totalActiveAmount ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="text-xs font-medium text-slate-400">IDR / Cycle</span>
                    </div>
                </div>

                <!-- Bottom Details Bar inside card -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <!-- Strictly 1 line status -->
                    <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 whitespace-nowrap truncate">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="truncate">{{ $dueSoonCount }} Sponsors due in ≤ 7 days</span>
                    </div>

                    <a 
                        href="{{ route('admin.sponsors.index') }}" 
                        class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] shrink-0 whitespace-nowrap"
                    >
                        Manage
                    </a>
                </div>
            </div>

            <!-- Right Card: Multi-Channel Gateway -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <!-- Card Top Bar -->
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="font-bold text-slate-900 text-sm">Multi-Channel Gateway</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Notification delivery status for WhatsApp, Telegram & Email</p>
                        </div>
                        <a 
                            href="{{ route('admin.settings.index') }}" 
                            class="w-8 h-8 rounded-xl border border-slate-200/90 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition"
                            title="Reminder Waves Settings"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="1"></circle>
                                <circle cx="19" cy="12" r="1"></circle>
                                <circle cx="5" cy="12" r="1"></circle>
                            </svg>
                        </a>
                    </div>

                    <!-- Visual Gateway Badge: Strictly 1 Line -->
                    <div class="mt-4 mb-4">
                        <div class="inline-flex items-center gap-2.5 px-3.5 py-2 rounded-xl bg-slate-900 text-white shadow-xs whitespace-nowrap max-w-full overflow-hidden">
                            <span class="text-[10px] font-bold tracking-widest uppercase bg-slate-800 px-2 py-0.5 rounded text-emerald-400 shrink-0">
                                READY
                            </span>
                            <span class="text-xs text-slate-300 font-medium whitespace-nowrap truncate">
                                WhatsApp Cloud • Telegram Bot • Email SMTP
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Details Bar inside card -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div class="truncate mr-2">
                        <div class="text-xs font-semibold text-slate-800 whitespace-nowrap truncate">
                            {{ $activeWavesCount }} Active Reminder Waves
                        </div>
                        <div class="text-[11px] text-slate-500 whitespace-nowrap truncate">
                            Today: {{ $todaySentCount }} sent, {{ $todayFailedCount }} failed
                        </div>
                    </div>

                    @if(Auth::user()->isSuperAdmin())
                        <a 
                            href="{{ route('admin.settings.index') }}" 
                            class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-200/90 text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 shadow-xs transition shrink-0 whitespace-nowrap"
                        >
                            Configure Waves
                        </a>
                    @else
                        <span class="text-xs text-slate-400 shrink-0 whitespace-nowrap">Configured</span>
                    @endif
                </div>
            </div>

        </div>

        <!-- 4. Main Inset Table Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs space-y-4">
            <!-- Header section of the table card -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-base">Upcoming Due Reminders</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Sponsors scheduled to receive reminder notifications soon</p>
                </div>
                <a href="{{ route('admin.sponsors.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition">
                    View All Sponsors →
                </a>
            </div>

            <!-- Inset Table with rounded frame -->
            <div class="border border-slate-200/70 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 border-b border-slate-200/70 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3">Sponsor & Beneficiary</th>
                                <th class="px-5 py-3">Frequency</th>
                                <th class="px-5 py-3">Commitment</th>
                                <th class="px-5 py-3">Target Due Date</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Action</th>
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
                                    <!-- Sponsor & Beneficiary -->
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-slate-900 text-xs">{{ $s->name }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            {{ $s->orphan_name ? 'Beneficiary: ' . $s->orphan_name : 'General Fund' }}
                                        </div>
                                    </td>

                                    <!-- Frequency -->
                                    <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium whitespace-nowrap">
                                            {{ $s->frequency->label() }}
                                        </span>
                                    </td>

                                    <!-- Amount -->
                                    <td class="px-5 py-3.5 font-medium text-rose-600 whitespace-nowrap">
                                        - {{ $s->formatted_amount }}
                                    </td>

                                    <!-- Billing Date -->
                                    <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                        {{ $item['next_due']->translatedFormat('d M Y') }} • 08:00 WIB
                                    </td>

                                    <!-- Status with leading bullet dot (Strictly 1 Line) -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @if($type === 'overdue')
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-rose-600 text-xs whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                                Overdue by {{ abs($diff) }} days
                                            </span>
                                        @elseif($type === 'due_soon')
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-amber-600 text-xs whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                                {{ $diff === 0 ? 'Due today' : 'In ' . $diff . ' days' }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-600 text-xs whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                                In {{ $diff }} days
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Action -->
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
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
                                        No active sponsors in the reminder queue currently.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table Footer / Pagination -->
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

        <!-- 5. Recent Delivery Activity Section -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Recent Delivery Activity</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Audit trail of latest notification dispatches by scheduler workers</p>
                </div>
                <a href="{{ route('admin.logs.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition">
                    All Logs →
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
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium whitespace-nowrap {{ $log->status->badgeClasses() }}">
                                    {{ $log->status->label() }}
                                </span>
                            </div>
                            <div class="mt-1.5 text-slate-500 text-[11px] flex items-center gap-1.5 whitespace-nowrap">
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
                        No recent delivery logs recorded.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
