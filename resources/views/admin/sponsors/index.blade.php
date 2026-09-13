<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Sponsors & Donors</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage donor records, commitment cycles, and notification preferences</p>
            </div>
            <div class="flex items-center gap-2.5">
                <!-- Direct Template Download Dropdown -->
                <div class="relative" x-data="{ templateDropdown: false }">
                    <button 
                        type="button"
                        @click="templateDropdown = !templateDropdown"
                        @click.outside="templateDropdown = false"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200/90 shadow-xs transition active:scale-[0.98] whitespace-nowrap cursor-pointer"
                        title="Download sample import template"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Download Template</span>
                        <svg class="w-3.5 h-3.5 text-slate-400 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div 
                        x-show="templateDropdown" 
                        x-cloak
                        x-transition:enter="ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-2xl border border-slate-200 shadow-xl py-2 z-50 divide-y divide-slate-100"
                    >
                        <div class="px-3.5 py-1.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Starter Templates</p>
                        </div>
                        <div class="py-1">
                            <a 
                                href="{{ route('admin.sponsors.import-template', ['format' => 'xlsx']) }}" 
                                class="flex items-center gap-2.5 px-3.5 py-2 text-slate-700 hover:bg-emerald-50/80 hover:text-emerald-700 transition text-xs font-medium group"
                            >
                                <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                                    XLS
                                </div>
                                <div class="text-left">
                                    <span class="block text-slate-900 font-medium">Excel Spreadsheet</span>
                                    <span class="block text-[10px] text-slate-400">.xlsx formatted file</span>
                                </div>
                            </a>
                            <a 
                                href="{{ route('admin.sponsors.import-template', ['format' => 'csv']) }}" 
                                class="flex items-center gap-2.5 px-3.5 py-2 text-slate-700 hover:bg-blue-50/80 hover:text-blue-700 transition text-xs font-medium group"
                            >
                                <div class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                                    CSV
                                </div>
                                <div class="text-left">
                                    <span class="block text-slate-900 font-medium">CSV Spreadsheet</span>
                                    <span class="block text-[10px] text-slate-400">Comma-separated values</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Import Excel / CSV Button -->
                <button 
                    type="button"
                    @click="$dispatch('open-import-modal')"
                    onclick="window.dispatchEvent(new CustomEvent('open-import-modal'))"
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-xs transition active:scale-[0.98] whitespace-nowrap cursor-pointer"
                >
                    <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                    <span>Import Excel / CSV</span>
                </button>
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

    <div 
        class="space-y-6" 
        x-data="{ 
            importModalOpen: false, 
            selectedFileName: '', 
            selectedFileSize: '', 
            duplicateMode: 'skip',
            isDragging: false,
            isSubmitting: false,
            guideOpen: false,
            handleFileDrop(e) {
                this.isDragging = false;
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    $refs.fileInput.files = e.dataTransfer.files;
                    this.selectedFileName = e.dataTransfer.files[0].name;
                    this.selectedFileSize = (e.dataTransfer.files[0].size / 1024).toFixed(1) + ' KB';
                }
            },
            handleFileChange(e) {
                if (e.target && e.target.files && e.target.files.length > 0) {
                    this.selectedFileName = e.target.files[0].name;
                    this.selectedFileSize = (e.target.files[0].size / 1024).toFixed(1) + ' KB';
                }
            },
            resetFile() {
                this.selectedFileName = '';
                this.selectedFileSize = '';
                if ($refs.fileInput) $refs.fileInput.value = '';
            }
        }"
        @open-import-modal.window="importModalOpen = true"
    >

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs flex items-center gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if(session('import_errors') && count(session('import_errors')) > 0)
            <div class="p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl text-xs space-y-2 shadow-xs" x-data="{ open: true }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 font-semibold text-amber-800">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>Import Completed with Warnings / Unprocessed Rows ({{ count(session('import_errors')) }})</span>
                    </div>
                    <button type="button" @click="open = !open" class="text-[11px] text-amber-700 hover:underline">
                        <span x-text="open ? 'Hide Details' : 'View Details'"></span>
                    </button>
                </div>
                <div x-show="open" class="pt-1">
                    <ul class="max-h-48 overflow-y-auto space-y-1 list-disc list-inside text-[11px] text-amber-800 bg-amber-100/50 p-3 rounded-xl">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
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

        <!-- Import Sponsors Excel/CSV Modal -->
        <div 
            x-show="importModalOpen" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div 
                x-show="importModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                @click="if(!isSubmitting) importModalOpen = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 sm:p-6 text-center">
                <div 
                    x-show="importModalOpen"
                    x-transition:enter="ease-out duration-250"
                    x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full sm:max-w-xl border border-slate-100 my-8"
                >
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shadow-sm shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 leading-snug" id="modal-title">Import Sponsors & Donors</h3>
                                <p class="text-xs text-slate-500 mt-1 leading-normal">Upload an Excel or CSV file to bulk create or update donors</p>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            @click="importModalOpen = false" 
                            :disabled="isSubmitting"
                            class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition disabled:opacity-50 cursor-pointer"
                            title="Close modal"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form 
                        method="POST" 
                        action="{{ route('admin.sponsors.import') }}" 
                        enctype="multipart/form-data"
                        @submit="isSubmitting = true"
                        class="p-6 space-y-5"
                    >
                        @csrf

                        <!-- Starter Template Download Row -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 bg-emerald-50/40 border border-emerald-100 rounded-xl">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-800 leading-tight">Download Starter Template</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5 leading-tight">Pre-formatted columns with sample donor records</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a 
                                    href="{{ route('admin.sponsors.import-template', ['format' => 'xlsx']) }}" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition active:scale-[0.98]"
                                    title="Download styled Excel template"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-3.5 14l-2.5-3.8-2.5 3.8h-2.3l3.6-5-3.5-5h2.3l2.4 3.7 2.4-3.7h2.3l-3.5 5 3.6 5h-2.3z"/>
                                    </svg>
                                    <span>Excel (.xlsx)</span>
                                </a>
                                <a 
                                    href="{{ route('admin.sponsors.import-template', ['format' => 'csv']) }}" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-lg text-xs font-semibold shadow-sm transition active:scale-[0.98]"
                                    title="Download CSV template"
                                >
                                    <span>CSV (.csv)</span>
                                </a>
                            </div>
                        </div>

                        <!-- File Upload Dropzone -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-800 mb-2">
                                Upload Spreadsheet File <span class="text-rose-500">*</span>
                            </label>
                            <div 
                                class="relative border-2 border-dashed rounded-xl p-6 text-center transition cursor-pointer"
                                :class="isDragging ? 'border-emerald-500 bg-emerald-50/50 ring-2 ring-emerald-500/20' : (selectedFileName ? 'border-emerald-300 bg-emerald-50/20' : 'border-slate-200 hover:border-slate-300 bg-slate-50/60 hover:bg-slate-50')"
                                @click="$refs.fileInput.click()"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleFileDrop($event)"
                            >
                                <input 
                                    x-ref="fileInput"
                                    type="file" 
                                    name="file" 
                                    accept=".xlsx,.xls,.csv,text/csv" 
                                    required 
                                    class="hidden"
                                    @change="handleFileChange($event)"
                                >

                                <!-- Empty / Unselected State -->
                                <div x-show="!selectedFileName" class="space-y-2.5 py-1">
                                    <div class="mx-auto w-12 h-12 rounded-2xl bg-white border border-slate-200/90 flex items-center justify-center text-slate-500 shadow-sm">
                                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-800">
                                            <span class="text-emerald-700 underline underline-offset-2">Click to choose a file</span> or drag and drop here
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-1">Supports Microsoft Excel (.xlsx, .xls) and CSV up to 10MB</p>
                                    </div>
                                </div>

                                <!-- Selected File State -->
                                <div x-show="selectedFileName" class="flex items-center justify-between p-3 bg-white rounded-xl border border-emerald-200 shadow-sm">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div class="text-left truncate">
                                            <p class="text-xs font-bold text-slate-900 truncate" x-text="selectedFileName"></p>
                                            <p class="text-[11px] text-emerald-600 font-medium flex items-center gap-1.5 mt-0.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span x-text="selectedFileSize + ' • Ready to import'"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click.stop="resetFile()"
                                        class="text-slate-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer shrink-0 ml-2"
                                        title="Remove file"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Duplicate Handling Mode -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-800 mb-2">
                                Duplicate Handling Mode <span class="text-slate-400 font-normal">(Matched by Email)</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Option: Skip -->
                                <label 
                                    class="relative flex items-start p-3.5 rounded-xl border cursor-pointer transition select-none"
                                    :class="duplicateMode === 'skip' ? 'border-emerald-500 bg-emerald-50/40 ring-1 ring-emerald-500/20' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                                >
                                    <input 
                                        type="radio" 
                                        name="duplicate_mode" 
                                        value="skip" 
                                        x-model="duplicateMode"
                                        class="mt-0.5 text-emerald-600 focus:ring-emerald-500 border-slate-300"
                                    >
                                    <div class="ml-2.5">
                                        <span class="block text-xs font-bold text-slate-900 leading-tight">Skip Duplicates</span>
                                        <span class="block text-[11px] text-slate-500 leading-snug mt-1">Keep existing donors untouched. Only add new records.</span>
                                    </div>
                                </label>

                                <!-- Option: Update -->
                                <label 
                                    class="relative flex items-start p-3.5 rounded-xl border cursor-pointer transition select-none"
                                    :class="duplicateMode === 'update' ? 'border-emerald-500 bg-emerald-50/40 ring-1 ring-emerald-500/20' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'"
                                >
                                    <input 
                                        type="radio" 
                                        name="duplicate_mode" 
                                        value="update" 
                                        x-model="duplicateMode"
                                        class="mt-0.5 text-emerald-600 focus:ring-emerald-500 border-slate-300"
                                    >
                                    <div class="ml-2.5">
                                        <span class="block text-xs font-bold text-slate-900 leading-tight">Update Existing</span>
                                        <span class="block text-[11px] text-slate-500 leading-snug mt-1">Overwrite matching donor fields with data from the file.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Format Reference Accordion -->
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/30">
                            <button 
                                type="button" 
                                @click="guideOpen = !guideOpen"
                                class="w-full px-4 py-3 text-left flex items-center justify-between text-xs font-semibold text-slate-700 hover:bg-slate-100/70 transition cursor-pointer"
                            >
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span>Column Specification & Format Rules</span>
                                </div>
                                <span class="text-slate-400 text-xs font-mono" x-text="guideOpen ? '▲' : '▼'"></span>
                            </button>
                            <div x-show="guideOpen" x-cloak class="p-4 border-t border-slate-200 bg-white">
                                <div class="overflow-x-auto max-h-56">
                                    <table class="w-full text-left text-[11px]">
                                        <thead>
                                            <tr class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                <th class="pb-2 pr-3">Header</th>
                                                <th class="pb-2 px-2.5">Status</th>
                                                <th class="pb-2 px-2.5">Example</th>
                                                <th class="pb-2 pl-3">Description</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-slate-600">
                                            <tr>
                                                <td class="py-2 pr-3 font-mono font-bold text-slate-900">name</td>
                                                <td class="py-2 px-2.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">Required</span></td>
                                                <td class="py-2 px-2.5 text-slate-500 whitespace-nowrap">H. Budi Prakoso</td>
                                                <td class="py-2 pl-3">Full donor name</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 pr-3 font-mono font-bold text-slate-900">email</td>
                                                <td class="py-2 px-2.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">Required</span></td>
                                                <td class="py-2 px-2.5 text-slate-500 whitespace-nowrap">budi@example.com</td>
                                                <td class="py-2 pl-3">Unique email address</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 pr-3 font-mono font-bold text-slate-900">phone</td>
                                                <td class="py-2 px-2.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">Required</span></td>
                                                <td class="py-2 px-2.5 text-slate-500 whitespace-nowrap">081234567890</td>
                                                <td class="py-2 pl-3">Auto-converted to E.164 (+62)</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 pr-3 font-mono font-bold text-slate-900">amount</td>
                                                <td class="py-2 px-2.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">Required</span></td>
                                                <td class="py-2 px-2.5 text-slate-500 whitespace-nowrap">500000</td>
                                                <td class="py-2 pl-3">Pledge amount (Rp / digits)</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 pr-3 font-mono font-bold text-slate-900">frequency</td>
                                                <td class="py-2 px-2.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">Required</span></td>
                                                <td class="py-2 px-2.5 font-mono text-slate-500 whitespace-nowrap">annual / 6_months</td>
                                                <td class="py-2 pl-3">Donation cycle</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 pr-3 font-mono font-bold text-slate-900">last_donation_date</td>
                                                <td class="py-2 px-2.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">Required</span></td>
                                                <td class="py-2 px-2.5 text-slate-500 whitespace-nowrap">2026-08-15</td>
                                                <td class="py-2 pl-3">YYYY-MM-DD or DD/MM/YYYY</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 pr-3 font-mono font-bold text-slate-900">orphan_name</td>
                                                <td class="py-2 px-2.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500">Optional</span></td>
                                                <td class="py-2 px-2.5 text-slate-500 whitespace-nowrap">Fatimah</td>
                                                <td class="py-2 pl-3">Beneficiary child name</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 pr-3 font-mono font-bold text-slate-900">status</td>
                                                <td class="py-2 px-2.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500">Optional</span></td>
                                                <td class="py-2 px-2.5 font-mono text-slate-500 whitespace-nowrap">active</td>
                                                <td class="py-2 pl-3">active, paused, cancelled</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 pr-3 font-mono font-bold text-slate-900">telegram_chat_id</td>
                                                <td class="py-2 px-2.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500">Optional</span></td>
                                                <td class="py-2 px-2.5 text-slate-500 whitespace-nowrap">998877</td>
                                                <td class="py-2 pl-3">Numeric Chat ID</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 pr-3 font-mono font-bold text-slate-900">channels</td>
                                                <td class="py-2 px-2.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500">Optional</span></td>
                                                <td class="py-2 px-2.5 text-slate-500 whitespace-nowrap">email, whatsapp</td>
                                                <td class="py-2 pl-3">Notification channels</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button 
                                type="button" 
                                @click="importModalOpen = false" 
                                :disabled="isSubmitting"
                                class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition disabled:opacity-50 cursor-pointer shadow-sm"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                :disabled="isSubmitting || !selectedFileName"
                                class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2 cursor-pointer"
                            >
                                <svg x-show="isSubmitting" class="animate-spin -ml-0.5 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span x-text="isSubmitting ? 'Uploading & Processing...' : 'Import Sponsors'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
