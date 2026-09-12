<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Custom Reminders</h1>
                <p class="text-xs text-slate-500 mt-0.5">Create custom scheduled reminders, broadcast announcements, and targeted donor notifications</p>
            </div>
            <div>
                <a 
                    href="{{ route('admin.custom-reminders.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] whitespace-nowrap"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Custom Reminder</span>
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

        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs flex items-center gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Quick Explanation Banner -->
        <div class="p-5 bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl text-white shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-teal-500/20 text-teal-300">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <h2 class="text-sm font-bold text-white">Standalone Custom Schedules & Broadcasts</h2>
                </div>
                <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                    Unlike standard Due-Date Waves, Custom Reminders run on custom schedules (hourly intervals, specific times of day, weekly, or one-off) and allow you to handpick exactly which donors will receive each notification.
                </p>
            </div>
            <div class="shrink-0 flex items-center gap-3 text-xs text-slate-300">
                <span class="px-3 py-1.5 rounded-xl bg-white/10 backdrop-blur-xs border border-white/10 font-mono">
                    Cron Active (Every Minute)
                </span>
            </div>
        </div>

        @if($reminders->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900 mb-1">No Custom Reminders Created Yet</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mb-4">
                    Create custom reminders to send periodic updates, special announcements, or targeted follow-ups to specific sponsors on your own schedule.
                </p>
                <a 
                    href="{{ route('admin.custom-reminders.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create First Custom Reminder</span>
                </a>
            </div>
        @else
            <!-- Reminders Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($reminders as $reminder)
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between hover:border-slate-300 transition">
                        <div class="space-y-4">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h2 class="font-bold text-slate-900 text-sm">
                                            {{ $reminder->title }}
                                        </h2>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold whitespace-nowrap {{ $reminder->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($reminder->schedule_type === 'once' && $reminder->last_run_at ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-600 border border-slate-200') }}">
                                            {{ $reminder->is_active ? 'Active' : ($reminder->schedule_type === 'once' && $reminder->last_run_at ? 'Completed' : 'Paused') }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1 flex-wrap text-[11px]">
                                        <span class="inline-flex items-center gap-1 font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $reminder->scheduleLabel() }}
                                        </span>
                                        <span class="text-slate-500 font-medium">
                                            Target: <strong class="text-slate-700">{{ $reminder->targetLabel() }}</strong>
                                        </span>
                                    </div>
                                </div>

                                <!-- Channels -->
                                <div class="flex items-center gap-1">
                                    @foreach($reminder->getChannelEnums() as $channel)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200/70 whitespace-nowrap">
                                            {{ $channel->label() }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Message Preview -->
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70 text-[11px] text-slate-700 font-mono leading-relaxed line-clamp-3">
                                {{ $reminder->message }}
                            </div>

                            <!-- Metrics & Schedule Status -->
                            <div class="grid grid-cols-3 gap-2 text-[11px] text-slate-600 bg-slate-50/50 p-3 rounded-xl border border-slate-100">
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Total Dispatched</span>
                                    <strong class="text-slate-900">{{ number_format($reminder->total_sent_count) }}</strong>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Last Execution</span>
                                    <span class="text-slate-700 font-medium">{{ $reminder->last_run_at ? $reminder->last_run_at->diffForHumans() : 'Never' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Next Run</span>
                                    <span class="text-slate-700 font-medium {{ $reminder->is_active ? 'text-indigo-600 font-semibold' : 'text-slate-400' }}">
                                        {{ $reminder->is_active && $reminder->next_run_at ? $reminder->next_run_at->diffForHumans() : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Actions Footer -->
                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-1.5">
                                <!-- Run Now Action -->
                                <form method="POST" action="{{ route('admin.custom-reminders.run-now', $reminder) }}" onsubmit="return confirm('Queue this custom reminder to trigger immediately to all targeted sponsors?');">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition active:scale-[0.98] cursor-pointer"
                                        title="Trigger immediate execution"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Run Now</span>
                                    </button>
                                </form>

                                <!-- Toggle Active/Pause -->
                                <form method="POST" action="{{ route('admin.custom-reminders.toggle', $reminder) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button 
                                        type="submit" 
                                        class="px-3 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-medium transition cursor-pointer"
                                    >
                                        {{ $reminder->is_active ? 'Pause' : 'Activate' }}
                                    </button>
                                </form>
                            </div>

                            <div class="flex items-center gap-2">
                                <a 
                                    href="{{ route('admin.custom-reminders.edit', $reminder) }}"
                                    class="px-3 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-medium transition"
                                >
                                    Edit
                                </a>

                                <form method="POST" action="{{ route('admin.custom-reminders.destroy', $reminder) }}" onsubmit="return confirm('Are you sure you want to delete this custom reminder?');">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit" 
                                        class="text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-2.5 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-app-layout>
