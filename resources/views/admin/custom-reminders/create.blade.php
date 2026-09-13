<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.custom-reminders.index') }}" class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-500 hover:text-slate-900 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Create Custom Reminder</h1>
                <p class="text-xs text-slate-500 mt-0.5">Configure schedule frequencies, custom notification messages, and target recipients</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs space-y-1 shadow-xs">
                <div class="font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>Please correct the errors below:</span>
                </div>
                <ul class="list-disc list-inside pl-1 text-[11px] text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.custom-reminders.store') }}" 
            x-data="{
                targetType: 'all_active',
                scheduleType: '{{ old('schedule_type', 'daily') }}',
                scheduleTime: '{{ old('schedule_time', '12:00') }}',
                multiTimes: {{ json_encode(old('schedule_times', ['08:00', '13:00', '18:00'])) }},
                newTimeInput: '',
                sponsorSearch: '',
                addMultiTime() {
                    if (this.newTimeInput && !this.multiTimes.includes(this.newTimeInput)) {
                        this.multiTimes.push(this.newTimeInput);
                        this.multiTimes.sort();
                        this.newTimeInput = '';
                    }
                },
                removeMultiTime(index) {
                    this.multiTimes.splice(index, 1);
                }
            }" 
            class="space-y-6"
        >
            @csrf

            <!-- Section 1: Reminder Details -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">1. Reminder Overview & Message</h2>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5 text-xs">Reminder Title <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        name="title" 
                        value="{{ old('title') }}" 
                        placeholder="e.g. Monthly Orphan Report Update or Friday Pledges Follow-up" 
                        required 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400"
                    >
                </div>

                <!-- Message Template -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block font-semibold text-slate-700 text-xs">Message Body <span class="text-rose-500">*</span></label>
                    </div>

                    <div class="flex flex-wrap gap-1 items-center pb-1">
                        <span class="text-[10px] text-slate-400 font-medium mr-1">Insert Tag:</span>
                        @foreach(['{sponsor_name}', '{orphan_name}', '{amount}', '{due_date}', '{payment_info}', '{platform_name}'] as $tag)
                            <button 
                                type="button" 
                                onclick="insertTag('custom-message', '{{ $tag }}')"
                                class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-mono rounded-md transition border border-slate-200/80 cursor-pointer"
                            >
                                + {{ $tag }}
                            </button>
                        @endforeach
                    </div>

                    <textarea 
                        id="custom-message" 
                        name="message" 
                        rows="5" 
                        required 
                        placeholder="Write your custom reminder message here. Dynamic tags will be replaced automatically per recipient..." 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 font-mono text-[11px] leading-relaxed focus:border-slate-400 focus:ring-slate-400"
                    >{{ old('message', "Assalamu'alaikum {sponsor_name},\n\nSemoga senantiasa dalam lindungan Allah SWT. Ini adalah pengingat untuk komitmen donasi anak asuh {orphan_name} sebesar {amount}.\n\n{payment_info}\n\nTerima kasih atas kebaikan Anda.\nSalam hangat,\n{platform_name}") }}</textarea>
                </div>

                <!-- Channels -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-2 text-xs">Active Delivery Channels <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        @foreach($availableChannels as $channel)
                            <label class="inline-flex items-center text-xs p-3 rounded-xl border border-slate-200/80 hover:bg-slate-50 cursor-pointer transition">
                                <input 
                                    type="checkbox" 
                                    name="channels[]" 
                                    value="{{ $channel->value }}" 
                                    {{ in_array($channel->value, old('channels', ['whatsapp', 'email'])) ? 'checked' : '' }}
                                    class="rounded border-slate-300 text-slate-900 shadow-xs focus:ring-slate-500"
                                >
                                <span class="ms-2.5 font-semibold text-slate-700">{{ $channel->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Section 2: Target Audience & Sponsor Picker -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">2. Target Audience</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition" :class="targetType === 'all_active' ? 'border-slate-900 bg-slate-50/80 ring-1 ring-slate-900' : 'border-slate-200 hover:bg-slate-50'">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="target_type" value="all_active" x-model="targetType" class="text-slate-900 focus:ring-slate-500">
                            <span class="text-xs font-bold text-slate-900">All Active Donors</span>
                        </div>
                        <span class="text-[10px] text-slate-500 mt-1 pl-5">Dispatches to every donor with Active account status</span>
                    </label>

                    <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition" :class="targetType === 'overdue' ? 'border-slate-900 bg-slate-50/80 ring-1 ring-slate-900' : 'border-slate-200 hover:bg-slate-50'">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="target_type" value="overdue" x-model="targetType" class="text-slate-900 focus:ring-slate-500">
                            <span class="text-xs font-bold text-slate-900">Overdue Donors Only</span>
                        </div>
                        <span class="text-[10px] text-slate-500 mt-1 pl-5">Filters only donors whose due date has passed</span>
                    </label>

                    <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition" :class="targetType === 'selected' ? 'border-slate-900 bg-slate-50/80 ring-1 ring-slate-900' : 'border-slate-200 hover:bg-slate-50'">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="target_type" value="selected" x-model="targetType" class="text-slate-900 focus:ring-slate-500">
                            <span class="text-xs font-bold text-slate-900">Specific Donors</span>
                        </div>
                        <span class="text-[10px] text-slate-500 mt-1 pl-5">Handpick specific donors using the selector below</span>
                    </label>
                </div>

                <!-- Sponsor Picker (Only shown if 'selected' is chosen) -->
                <div x-show="targetType === 'selected'" x-collapse class="space-y-3 pt-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 bg-slate-50 p-3 rounded-xl border border-slate-200/80">
                        <div class="relative w-full sm:w-64">
                            <input 
                                type="text" 
                                x-model="sponsorSearch" 
                                placeholder="Search donor or orphan..." 
                                class="w-full text-xs rounded-lg border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 pl-8 py-1.5 focus:border-slate-400 focus:ring-slate-400"
                            >
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <button 
                                type="button" 
                                onclick="document.querySelectorAll('.sponsor-checkbox').forEach(cb => cb.checked = true)"
                                class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-[11px] font-medium text-slate-700 transition"
                            >
                                Select All
                            </button>
                            <button 
                                type="button" 
                                onclick="document.querySelectorAll('.sponsor-checkbox').forEach(cb => cb.checked = false)"
                                class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-[11px] font-medium text-slate-700 transition"
                            >
                                Clear All
                            </button>
                        </div>
                    </div>

                    <div class="max-h-64 overflow-y-auto rounded-xl border border-slate-200 divide-y divide-slate-100">
                        @foreach($sponsors as $sponsor)
                            <label 
                                class="flex items-center justify-between p-2.5 hover:bg-slate-50 cursor-pointer transition sponsor-row text-xs"
                                x-show="sponsorSearch === '' || '{{ strtolower($sponsor->name . ' ' . $sponsor->orphan_name . ' ' . $sponsor->email . ' ' . $sponsor->phone) }}'.includes(sponsorSearch.toLowerCase())"
                            >
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="checkbox" 
                                        name="sponsor_ids[]" 
                                        value="{{ $sponsor->id }}" 
                                        class="sponsor-checkbox rounded border-slate-300 text-slate-900 shadow-xs focus:ring-slate-500"
                                    >
                                    <div>
                                        <span class="font-semibold text-slate-900 block">{{ $sponsor->name }}</span>
                                        <span class="text-[11px] text-slate-400">Child: {{ $sponsor->orphan_name ?: '-' }} &bull; {{ $sponsor->formatted_amount }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px]">
                                    @if($sponsor->phone)
                                        <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">WA</span>
                                    @endif
                                    @if($sponsor->hasConnectedTelegram())
                                        <span class="px-1.5 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200 font-mono">TG</span>
                                    @endif
                                    @if($sponsor->email)
                                        <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 font-mono">Email</span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Section 3: Schedule Configurator -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2 gap-2 flex-wrap">
                    <h2 class="text-sm font-bold text-slate-900">3. Schedule Configuration</h2>
                    <span class="text-[11px] font-mono font-medium text-slate-600 bg-slate-100/80 px-2.5 py-0.5 rounded-md border border-slate-200/80">
                        Active Time: <strong class="text-slate-900">{{ now()->format('H:i') }} {{ $platformTimezoneLabel }}</strong>
                    </span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5 text-xs">Schedule Frequency Type <span class="text-rose-500">*</span></label>
                    <select name="schedule_type" x-model="scheduleType" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                        <option value="daily">Daily at Specific Time (Harian)</option>
                        <option value="multiple_daily">Multiple Times a Day (Sehari Beberapa Kali, misal 3x)</option>
                        <option value="interval_hours">Hourly Interval (Setiap X Jam, misal tiap 2 jam)</option>
                        <option value="weekly">Weekly (Mingguan pada hari tertentu)</option>
                        <option value="once">One-Time (Sekali kirim pada tanggal tertentu)</option>
                    </select>
                </div>

                <!-- Mode 1: Daily Single Time -->
                <div x-show="scheduleType === 'daily'" class="p-4 bg-slate-50/80 rounded-xl border border-slate-200/70 space-y-1">
                    <label class="block font-semibold text-slate-700 text-xs mb-1">Execution Time ({{ $platformTimezoneLabel }})</label>
                    <input type="time" name="schedule_time" x-model="scheduleTime" :disabled="scheduleType !== 'daily'" class="text-xs rounded-xl border-slate-200 bg-white text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                    <p class="text-[10px] text-slate-400">Dispatches once every day at this exact time ({{ $platformTimezoneLabel }}).</p>
                </div>

                <!-- Mode 2: Multiple Times a Day -->
                <div x-show="scheduleType === 'multiple_daily'" class="p-4 bg-slate-50/80 rounded-xl border border-slate-200/70 space-y-3">
                    <div>
                        <label class="block font-semibold text-slate-700 text-xs mb-1">Multiple Daily Times ({{ $platformTimezoneLabel }})</label>
                        <p class="text-[10px] text-slate-400 mb-2">Specify all times during the day when this reminder should trigger.</p>
                        
                        <div class="flex items-center gap-2 mb-3">
                            <input type="time" x-model="newTimeInput" class="text-xs rounded-xl border-slate-200 bg-white text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                            <button type="button" @click="addMultiTime()" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition">
                                + Add Time
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-2 items-center">
                            <template x-for="(time, idx) in multiTimes" :key="idx">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 text-slate-800 rounded-lg text-xs font-mono shadow-xs">
                                    <span x-text="time + ' ' + '{{ $platformTimezoneLabel }}'"></span>
                                    <input type="hidden" name="schedule_times[]" :value="time" :disabled="scheduleType !== 'multiple_daily'">
                                    <button type="button" @click="removeMultiTime(idx)" class="text-rose-500 hover:text-rose-700 font-bold ml-1">&times;</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Mode 3: Hourly Interval -->
                <div x-show="scheduleType === 'interval_hours'" class="p-4 bg-slate-50/80 rounded-xl border border-slate-200/70 space-y-1">
                    <label class="block font-semibold text-slate-700 text-xs mb-1">Repeat Every</label>
                    <select name="interval_hours" :disabled="scheduleType !== 'interval_hours'" class="text-xs rounded-xl border-slate-200 bg-white text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                        <option value="1">Every 1 Hour</option>
                        <option value="2" selected>Every 2 Hours (Setiap 2 jam)</option>
                        <option value="3">Every 3 Hours</option>
                        <option value="4">Every 4 Hours</option>
                        <option value="6">Every 6 Hours</option>
                        <option value="12">Every 12 Hours</option>
                        <option value="24">Every 24 Hours</option>
                    </select>
                    <p class="text-[10px] text-slate-400">Triggers continuously at the configured hourly interval.</p>
                </div>

                <!-- Mode 4: Weekly -->
                <div x-show="scheduleType === 'weekly'" class="p-4 bg-slate-50/80 rounded-xl border border-slate-200/70 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 text-xs mb-1">Day of Week</label>
                        <select name="schedule_day" :disabled="scheduleType !== 'weekly'" class="w-full text-xs rounded-xl border-slate-200 bg-white text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                            @foreach($daysOfWeek as $num => $dayName)
                                <option value="{{ $num }}" {{ $num === 1 ? 'selected' : '' }}>{{ $dayName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 text-xs mb-1">Time ({{ $platformTimezoneLabel }})</label>
                        <input type="time" name="schedule_time" x-model="scheduleTime" :disabled="scheduleType !== 'weekly'" class="w-full text-xs rounded-xl border-slate-200 bg-white text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                    </div>
                </div>

                <!-- Mode 5: Once -->
                <div x-show="scheduleType === 'once'" class="p-4 bg-slate-50/80 rounded-xl border border-slate-200/70 space-y-1">
                    <label class="block font-semibold text-slate-700 text-xs mb-1">Dispatch Date & Time ({{ $platformTimezoneLabel }})</label>
                    <input type="datetime-local" name="scheduled_at" :disabled="scheduleType !== 'once'" class="text-xs rounded-xl border-slate-200 bg-white text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                    <p class="text-[10px] text-slate-400">Triggers exactly once at this timestamp, then automatically marks as completed.</p>
                </div>

                <!-- Active Toggle -->
                <div class="pt-3 border-t border-slate-100">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-900"></div>
                        <span class="ms-2 text-xs font-semibold text-slate-700 whitespace-nowrap">
                            Enable and activate schedule immediately
                        </span>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a 
                    href="{{ route('admin.custom-reminders.index') }}" 
                    class="px-5 py-2.5 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold transition"
                >
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] cursor-pointer"
                >
                    Save & Schedule Reminder
                </button>
            </div>
        </form>

    </div>

    <script>
        function insertTag(textareaId, tag) {
            const el = document.getElementById(textareaId);
            if (!el) return;
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const text = el.value;
            el.value = text.substring(0, start) + tag + text.substring(end);
            el.focus();
            el.selectionStart = el.selectionEnd = start + tag.length;
        }
    </script>
</x-app-layout>
