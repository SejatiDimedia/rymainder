<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Sponsors & Donors</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage donor records, commitment cycles, and notification preferences</p>
            </div>
            <div>
                <a 
                    href="{{ route('admin.sponsors.create') }}" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl shadow-xs transition active:scale-[0.98] whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add New Sponsor
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

        <!-- Filter & Search Bar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('admin.sponsors.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Search Sponsor / Beneficiary</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search by name, email, phone..." 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:ring-slate-400"
                    >
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sponsor Status</label>
                    <select 
                        name="status" 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400"
                    >
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Frequency Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Donation Frequency</label>
                    <select 
                        name="frequency" 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400"
                    >
                        <option value="">All Frequencies</option>
                        @foreach($frequencies as $freq)
                            <option value="{{ $freq->value }}" {{ request('frequency') === $freq->value ? 'selected' : '' }}>
                                {{ $freq->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end gap-2">
                    <button 
                        type="submit" 
                        class="w-full px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition"
                    >
                        Apply Filters
                    </button>
                    @if(request()->hasAny(['search', 'status', 'frequency']))
                        <a 
                            href="{{ route('admin.sponsors.index') }}" 
                            class="px-3.5 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-medium transition whitespace-nowrap"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Main Inset Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">All Registered Sponsors</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Monitoring cycle status and notification delivery readiness</p>
                </div>
                <div class="text-xs text-slate-500 whitespace-nowrap">
                    Total: <span class="font-semibold text-slate-800">{{ $sponsors->total() }}</span> sponsors
                </div>
            </div>

            <!-- Inset Table -->
            <div class="border border-slate-200/70 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/70 border-b border-slate-200/70 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3.5">Sponsor & Contact</th>
                                <th class="px-5 py-3.5">Beneficiary</th>
                                <th class="px-5 py-3.5">Commitment</th>
                                <th class="px-5 py-3.5">Next Due Date</th>
                                <th class="px-5 py-3.5">Due Status</th>
                                <th class="px-5 py-3.5">Account Status</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($sponsors as $sponsor)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <!-- Sponsor Info -->
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-slate-900 text-xs">
                                            <a href="{{ route('admin.sponsors.show', $sponsor) }}" class="hover:text-blue-600 transition">
                                                {{ $sponsor->name }}
                                            </a>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            {{ $sponsor->email }} • {{ $sponsor->phone }}
                                        </div>
                                        <div class="mt-1 flex items-center gap-1.5 whitespace-nowrap">
                                            @if($sponsor->hasConnectedTelegram())
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-sky-50 text-sky-700 border border-sky-200/60">
                                                    Telegram Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-500">
                                                    Telegram Pending
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Beneficiary -->
                                    <td class="px-5 py-3.5 text-slate-700 font-medium">
                                        {{ $sponsor->orphan_name ?? '—' }}
                                    </td>

                                    <!-- Commitment -->
                                    <td class="px-5 py-3.5 font-semibold text-slate-900 whitespace-nowrap">
                                        {{ $sponsor->formatted_amount }}
                                        <div class="text-[10px] text-slate-400 font-normal mt-0.5">
                                            {{ $sponsor->frequency->label() }}
                                        </div>
                                    </td>

                                    <!-- Next Due Date -->
                                    <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                        <div class="font-medium text-slate-800">
                                            {{ $sponsor->computed_next_due->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            Last: {{ $sponsor->last_donation_date->translatedFormat('d M Y') }}
                                        </div>
                                    </td>

                                    <!-- Due Status (Strictly 1 Line) -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @if($sponsor->days_diff < 0)
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-rose-600 text-xs whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                                Overdue by {{ abs($sponsor->days_diff) }} days
                                            </span>
                                        @elseif($sponsor->days_diff === 0)
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-amber-600 text-xs whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                                Due today
                                            </span>
                                        @elseif($sponsor->days_diff <= 7)
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-amber-600 text-xs whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                                In {{ $sponsor->days_diff }} days
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-600 text-xs whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                                In {{ $sponsor->days_diff }} days
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Account Status -->
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium whitespace-nowrap {{ $sponsor->status->badgeClasses() }}">
                                            {{ $sponsor->status->label() }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1">
                                        <a 
                                            href="{{ route('admin.sponsors.show', $sponsor) }}" 
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 transition shadow-2xs"
                                        >
                                            Details
                                        </a>
                                        <a 
                                            href="{{ route('admin.sponsors.edit', $sponsor) }}" 
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 transition shadow-2xs"
                                        >
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-slate-400 text-xs">
                                        No sponsors found matching the selected filter criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table Pagination -->
            <div class="pt-2">
                {{ $sponsors->links() }}
            </div>
        </div>

    </div>
</x-app-layout>
