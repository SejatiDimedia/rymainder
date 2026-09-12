<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Reminder Wave Settings</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage delivery schedule rules (H-7, H-3, Due Date, Overdue) and active notification channels</p>
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

        <!-- Sub-Navigation Tabs: Platform Branding | Reminder Waves -->
        <div class="flex items-center gap-1 p-1 bg-slate-200/50 rounded-xl w-fit text-xs">
            <a href="{{ route('admin.settings.platform') }}" class="px-4 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition">
                Platform & Branding
            </a>
            <span class="px-4 py-1.5 rounded-lg bg-white font-semibold text-slate-900 shadow-xs cursor-default">
                Reminder Waves
            </span>
        </div>

        <!-- Info Guide Card -->
        <div class="p-5 bg-indigo-50/70 border border-indigo-100 rounded-2xl text-xs text-indigo-950 leading-relaxed shadow-xs">
            <div class="font-bold mb-1.5 flex items-center gap-2 text-indigo-900">
                <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Reminder Wave Rules Engine Guide:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-indigo-900/80 mt-2">
                <li><strong>Positive value (e.g. 7):</strong> Reminder is dispatched 7 days prior to target due date (H-7).</li>
                <li><strong>Value 0:</strong> Reminder is dispatched on the exact due date (Due Date / H-0).</li>
                <li><strong>Negative value (e.g. -7):</strong> Overdue follow-up reminder is dispatched 7 days after due date has passed.</li>
                <li>Each wave can configure individual channels to optimize Meta WhatsApp Cloud API costs.</li>
            </ul>
        </div>

        <!-- Waves Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($settings as $setting)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
                    <form method="POST" action="{{ route('admin.settings.update', $setting) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <h2 class="font-bold text-slate-900 text-sm">
                                    {{ $setting->label }}
                                </h2>
                                <span class="text-[11px] text-slate-400">
                                    Target: {{ $setting->days_before_due > 0 ? 'H-' . $setting->days_before_due : ($setting->days_before_due === 0 ? 'Due Date' : 'Overdue +' . abs($setting->days_before_due) . ' days') }}
                                </span>
                            </div>

                            <!-- Toggle Switch -->
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ $setting->is_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-900"></div>
                                <span class="ms-2 text-xs font-semibold text-slate-700 whitespace-nowrap">
                                    {{ $setting->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1.5">Wave Label</label>
                                <input type="text" name="label" value="{{ $setting->label }}" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 mb-1.5">Days Before Due Date</label>
                                <input type="number" name="days_before_due" value="{{ $setting->days_before_due }}" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                                <p class="text-[10px] text-slate-400 mt-1">Use negative values (-) for overdue days</p>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-2 text-xs">Active Notification Channels</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($availableChannels as $channel)
                                    <label class="inline-flex items-center text-xs p-2.5 rounded-xl border border-slate-200/70 hover:bg-slate-50 cursor-pointer transition">
                                        <input type="checkbox" name="channels[]" value="{{ $channel->value }}" 
                                            {{ $setting->hasChannel($channel) ? 'checked' : '' }}
                                            class="rounded border-slate-300 text-slate-900 shadow-xs focus:ring-slate-500">
                                        <span class="ms-2 font-medium text-slate-700 whitespace-nowrap">{{ $channel->label() }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] whitespace-nowrap">
                                Save Wave Settings
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>

    </div>
</x-app-layout>
