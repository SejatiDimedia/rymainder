<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Reminder Delivery Logs</h1>
                <p class="text-xs text-slate-500 mt-0.5">Centralized audit trail of all notifications dispatched across Email, WhatsApp, and Telegram</p>
            </div>
        </div>
    </x-slot>

    <div 
        x-data="{
            bulkRetryModalOpen: false,
            singleRetryModalOpen: false,
            singleRetryUrl: '',
            singleRetrySponsor: '',
            singleRetryChannel: '',
            singleRetryWave: '',
            singleRetryDue: '',
            singleRetryError: '',
            isSubmitting: false,
            openSingleRetry(url, sponsor, channel, wave, due, error) {
                this.singleRetryUrl = url;
                this.singleRetrySponsor = sponsor;
                this.singleRetryChannel = channel;
                this.singleRetryWave = wave;
                this.singleRetryDue = due;
                this.singleRetryError = error;
                this.singleRetryModalOpen = true;
            }
        }"
        @keydown.escape.window="if (!isSubmitting) { bulkRetryModalOpen = false; singleRetryModalOpen = false; }"
        class="space-y-6"
    >

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl text-xs flex items-center gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium">{{ session('info') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs flex items-center gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if(isset($failedCount) && $failedCount > 0)
            <div class="p-4 bg-rose-50 border border-rose-200/80 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-rose-900">
                            Attention: {{ $failedCount }} Failed {{ Str::plural('Reminder', $failedCount) }} Detected
                        </h4>
                        <p class="text-[11px] text-rose-700 mt-0.5">
                            Notifications failed to deliver due to provider timeout or connection issue. You can resend them safely into the queue.
                        </p>
                    </div>
                </div>
                <div class="shrink-0 w-full sm:w-auto">
                    <button 
                        type="button" 
                        @click="bulkRetryModalOpen = true"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-semibold shadow-xs transition cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Retry All Failed Reminders ({{ $failedCount }})
                    </button>
                </div>
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
                                        {{ $log->reminderSetting->label ?? ($log->customReminder->title ?? ($log->is_manual ? 'Manual Direct' : 'Custom Direct')) }}
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
                                            <button 
                                                type="button" 
                                                data-url="{{ route('admin.logs.retry', $log) }}"
                                                data-sponsor="{{ $log->sponsor->name ?? '[Deleted]' }}"
                                                data-channel="{{ $log->channel->label() }}"
                                                data-wave="{{ $log->reminderSetting->label ?? ($log->customReminder->title ?? ($log->is_manual ? 'Manual Direct' : 'Custom Direct')) }}"
                                                data-due="{{ $log->due_date->translatedFormat('d M Y') }}"
                                                data-error="{{ $log->error_message ?? '' }}"
                                                @click="openSingleRetry($el.dataset.url, $el.dataset.sponsor, $el.dataset.channel, $el.dataset.wave, $el.dataset.due, $el.dataset.error)"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 active:bg-rose-200 rounded-lg border border-rose-200 transition cursor-pointer"
                                            >
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                Retry Send
                                            </button>
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

        <!-- Bulk Retry Confirmation Modal -->
        <div 
            x-show="bulkRetryModalOpen" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="bulk-retry-title" 
            role="dialog" 
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div 
                x-show="bulkRetryModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                @click="if(!isSubmitting) bulkRetryModalOpen = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 sm:p-6 text-center">
                <div 
                    x-show="bulkRetryModalOpen"
                    x-transition:enter="ease-out duration-250"
                    x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full sm:max-w-md border border-slate-100 my-8"
                >
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-rose-50/40">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shadow-xs shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" id="bulk-retry-title">Confirm Bulk Resend</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Queue all failed reminder notifications</p>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            @click="bulkRetryModalOpen = false" 
                            :disabled="isSubmitting"
                            class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition disabled:opacity-50 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <p class="text-xs text-slate-600 leading-relaxed">
                            You are about to requeue <span class="font-bold text-slate-900">{{ $failedCount ?? 0 }} failed reminders</span> for background dispatch.
                        </p>

                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2 text-xs">
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Failed Reminders:</span>
                                <span class="font-bold text-rose-700">{{ $failedCount ?? 0 }} notifications</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Execution Channel:</span>
                                <span class="font-semibold text-slate-800">{{ request('channel') ? ucfirst(request('channel')) : 'All Channels' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Double-Send Protection:</span>
                                <span class="font-semibold text-emerald-700 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Guaranteed Zero Duplicates
                                </span>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-400 leading-normal">
                            All pending and successful records will remain untouched. Only currently failed logs will be transitioned to pending and queued for worker execution.
                        </p>
                    </div>

                    <!-- Footer -->
                    <form method="POST" action="{{ route('admin.logs.retry-all') }}" @submit="isSubmitting = true">
                        @csrf
                        @if(request('channel'))
                            <input type="hidden" name="channel" value="{{ request('channel') }}">
                        @endif
                        <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end gap-3">
                            <button 
                                type="button" 
                                @click="bulkRetryModalOpen = false" 
                                :disabled="isSubmitting"
                                class="px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-xl border border-slate-200 transition cursor-pointer disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                :disabled="isSubmitting"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer disabled:opacity-75"
                            >
                                <svg x-show="isSubmitting" class="animate-spin -ml-0.5 w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <svg x-show="!isSubmitting" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span x-text="isSubmitting ? 'Queueing Retries...' : 'Yes, Resend All'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Single Retry Confirmation Modal -->
        <div 
            x-show="singleRetryModalOpen" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="single-retry-title" 
            role="dialog" 
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div 
                x-show="singleRetryModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                @click="if(!isSubmitting) singleRetryModalOpen = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 sm:p-6 text-center">
                <div 
                    x-show="singleRetryModalOpen"
                    x-transition:enter="ease-out duration-250"
                    x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full sm:max-w-md border border-slate-100 my-8"
                >
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-sky-50/40">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center shadow-xs shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" id="single-retry-title">Confirm Reminder Resend</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Queue notification for immediate redelivery</p>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            @click="singleRetryModalOpen = false" 
                            :disabled="isSubmitting"
                            class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 flex items-center justify-center transition disabled:opacity-50 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Recipient:</span>
                                <span class="font-bold text-slate-900" x-text="singleRetrySponsor"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Notification Channel:</span>
                                <span class="font-semibold text-sky-700" x-text="singleRetryChannel"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Reminder Schedule:</span>
                                <span class="font-medium text-slate-800" x-text="singleRetryWave"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Target Due Date:</span>
                                <span class="font-medium text-slate-700" x-text="singleRetryDue"></span>
                            </div>
                        </div>

                        <!-- Previous Error Callout -->
                        <div x-show="singleRetryError" class="p-3 bg-rose-50 border border-rose-100 rounded-xl text-[11px] text-rose-700">
                            <p class="font-semibold text-rose-800 mb-0.5">Previous Delivery Error:</p>
                            <p class="font-mono break-words leading-relaxed" x-text="singleRetryError"></p>
                        </div>

                        <p class="text-[11px] text-slate-400 leading-normal">
                            Submitting will reset the status to pending and dispatch through the background worker.
                        </p>
                    </div>

                    <!-- Footer -->
                    <form method="POST" :action="singleRetryUrl" @submit="isSubmitting = true">
                        @csrf
                        <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end gap-3">
                            <button 
                                type="button" 
                                @click="singleRetryModalOpen = false" 
                                :disabled="isSubmitting"
                                class="px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-xl border border-slate-200 transition cursor-pointer disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                :disabled="isSubmitting"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer disabled:opacity-75"
                            >
                                <svg x-show="isSubmitting" class="animate-spin -ml-0.5 w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <svg x-show="!isSubmitting" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span x-text="isSubmitting ? 'Sending...' : 'Confirm Resend'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
