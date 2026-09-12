<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Reminder Wave Settings</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage automated due-date reminder waves, active delivery channels, and custom message templates</p>
            </div>
            <div>
                <button 
                    type="button" 
                    @click="$dispatch('open-create-wave-modal')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] whitespace-nowrap cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Custom Wave</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div x-data="{ createModalOpen: false }" @open-create-wave-modal.window="createModalOpen = true" class="space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center gap-2.5 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs space-y-1 shadow-xs">
                <div class="font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>Please correct the errors below:</span>
                </div>
                <ul class="list-disc list-inside pl-1 text-[11px] text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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

        <!-- Automated Daily Engine Schedule Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-bold text-slate-900">Automated Daily Schedule</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80 whitespace-nowrap">
                            System Engine Active
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                        The background engine triggers once daily at this exact time to scan all active sponsors against the due-date waves below. Donors matching any active wave are automatically queued and notified.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.settings.schedule-time') }}" class="flex items-center gap-2.5 shrink-0">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <label for="reminder_dispatch_time" class="text-xs font-semibold text-slate-600 whitespace-nowrap">Daily Run at:</label>
                        <input 
                            type="time" 
                            id="reminder_dispatch_time" 
                            name="reminder_dispatch_time" 
                            value="{{ $dispatchTime }}" 
                            required 
                            class="text-xs font-bold rounded-lg border-slate-300 bg-white text-slate-900 focus:border-slate-400 focus:ring-slate-400 py-1 px-2"
                        >
                        <span class="text-xs font-semibold text-slate-400">WIB</span>
                    </div>

                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] whitespace-nowrap cursor-pointer"
                    >
                        Save Time
                    </button>
                </form>
            </div>
        </div>

        <!-- Info Guide Card -->
        <div class="p-5 bg-indigo-50/70 border border-indigo-100 rounded-2xl text-xs text-indigo-950 leading-relaxed shadow-xs">
            <div class="font-bold mb-1.5 flex items-center gap-2 text-indigo-900">
                <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>How Reminder Waves Work:</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-indigo-900/80 mt-2">
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Due Date Offsets:</strong> Positive values (e.g. 7 for H-7), 0 (Due Date), or Negative values (e.g. -7 for Overdue follow-up).</li>
                    <li><strong>Daily Automated Scan:</strong> At {{ $dispatchTime }} WIB each day, the scheduler matches all donors whose due date equals today ± wave offset.</li>
                    <li><strong>Zero Duplicate Guarantee:</strong> Donors are only ever notified once per wave per donation cycle via the unique audit log.</li>
                </ul>
                <div class="space-y-1">
                    <p><strong>Available Dynamic Merge Tags:</strong></p>
                    <p class="text-[11px] leading-relaxed">
                        <code class="bg-indigo-100/80 px-1 py-0.5 rounded text-indigo-900 font-mono">{sponsor_name}</code>,
                        <code class="bg-indigo-100/80 px-1 py-0.5 rounded text-indigo-900 font-mono">{orphan_name}</code>,
                        <code class="bg-indigo-100/80 px-1 py-0.5 rounded text-indigo-900 font-mono">{amount}</code>,
                        <code class="bg-indigo-100/80 px-1 py-0.5 rounded text-indigo-900 font-mono">{due_date}</code>,
                        <code class="bg-indigo-100/80 px-1 py-0.5 rounded text-indigo-900 font-mono">{status_text}</code>,
                        <code class="bg-indigo-100/80 px-1 py-0.5 rounded text-indigo-900 font-mono">{wave_label}</code>,
                        <code class="bg-indigo-100/80 px-1 py-0.5 rounded text-indigo-900 font-mono">{payment_info}</code>,
                        <code class="bg-indigo-100/80 px-1 py-0.5 rounded text-indigo-900 font-mono">{platform_name}</code>
                    </p>
                </div>
            </div>
        </div>

        <!-- Waves Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($settings as $setting)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between" x-data="{ expandedTemplate: {{ !empty($setting->message_template) ? 'true' : 'false' }} }">
                    <form id="form-setting-{{ $setting->id }}" method="POST" action="{{ route('admin.settings.update', $setting) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="font-bold text-slate-900 text-sm">
                                        {{ $setting->label }}
                                    </h2>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold whitespace-nowrap {{ $setting->days_before_due > 0 ? 'bg-blue-50 text-blue-700 border border-blue-200' : ($setting->days_before_due === 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                        {{ $setting->days_before_due > 0 ? 'H-' . $setting->days_before_due : ($setting->days_before_due === 0 ? 'Due Date (H-0)' : 'Overdue +' . abs($setting->days_before_due) . 'd') }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-400 block mt-0.5">
                                    Target: {{ $setting->days_before_due > 0 ? $setting->days_before_due . ' days before due' : ($setting->days_before_due === 0 ? 'On due date' : abs($setting->days_before_due) . ' days after due date') }}
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

                        <!-- Label and Due Offset -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1.5">Wave Label</label>
                                <input type="text" name="label" value="{{ $setting->label }}" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 mb-1.5">Days Before Due Date</label>
                                <input type="number" name="days_before_due" value="{{ $setting->days_before_due }}" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                                <p class="text-[10px] text-slate-400 mt-1">Negative numbers = overdue follow-ups</p>
                            </div>
                        </div>

                        <!-- Channels -->
                        <div>
                            <label class="block font-semibold text-slate-700 mb-2 text-xs">Active Notification Channels</label>
                            <div class="grid grid-cols-3 gap-2">
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

                        <!-- Message Template Section -->
                        <div class="pt-3 border-t border-slate-100">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block font-semibold text-slate-700 text-xs">
                                    Message Template
                                    @if(empty($setting->message_template))
                                        <span class="text-[10px] font-normal text-slate-400 ml-1">(Using Platform Default)</span>
                                    @else
                                        <span class="text-[10px] font-semibold text-emerald-600 ml-1">(Customized)</span>
                                    @endif
                                </label>
                                <button 
                                    type="button" 
                                    @click="expandedTemplate = !expandedTemplate" 
                                    class="text-[11px] font-medium text-slate-500 hover:text-slate-900 transition flex items-center gap-1 cursor-pointer"
                                >
                                    <span x-text="expandedTemplate ? 'Hide Template' : 'Edit Template'"></span>
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="expandedTemplate ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>

                            <div x-show="expandedTemplate" x-collapse class="space-y-2">
                                <div class="flex flex-wrap gap-1 items-center pb-1">
                                    <span class="text-[10px] text-slate-400 font-medium mr-1">Insert Tag:</span>
                                    @foreach(['{sponsor_name}', '{orphan_name}', '{amount}', '{due_date}', '{status_text}', '{wave_label}', '{payment_info}', '{platform_name}'] as $tag)
                                        <button 
                                            type="button" 
                                            onclick="insertTag('template-{{ $setting->id }}', '{{ $tag }}')"
                                            class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-mono rounded-md transition border border-slate-200/80 cursor-pointer"
                                        >
                                            + {{ $tag }}
                                        </button>
                                    @endforeach
                                </div>

                                <textarea 
                                    id="template-{{ $setting->id }}" 
                                    name="message_template" 
                                    rows="5" 
                                    placeholder="Leave empty to use the standard notification message format..."
                                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 font-mono text-[11px] leading-relaxed focus:border-slate-400 focus:ring-slate-400"
                                >{{ $setting->message_template }}</textarea>
                                <p class="text-[10px] text-slate-400">Leave empty to use the default courteous message with payment info.</p>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <button 
                                    type="button"
                                    onclick="if(confirm('Are you sure you want to delete this reminder wave rule?')) { document.getElementById('delete-wave-{{ $setting->id }}').submit(); }"
                                    class="text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-2.5 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer"
                                >
                                    Delete Wave
                                </button>
                            </div>

                            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] whitespace-nowrap cursor-pointer">
                                Save Wave Settings
                            </button>
                        </div>
                    </form>

                    <!-- Hidden Delete Form -->
                    <form id="delete-wave-{{ $setting->id }}" method="POST" action="{{ route('admin.settings.destroy', $setting) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            @endforeach
        </div>

        <!-- Add Custom Wave Modal -->
        <div 
            x-show="createModalOpen" 
            x-cloak 
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
        >
            <!-- Backdrop -->
            <div 
                x-show="createModalOpen" 
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0" 
                x-transition:enter-end="opacity-100" 
                x-transition:leave="ease-in duration-200" 
                x-transition:leave-start="opacity-100" 
                x-transition:leave-end="opacity-0" 
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                @click="createModalOpen = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div 
                    x-show="createModalOpen" 
                    x-transition:enter="ease-out duration-300" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave="ease-in duration-200" 
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl p-6 space-y-5"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-title">Create New Reminder Wave</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Configure a due-date offset rule, delivery channels, and custom message template</p>
                        </div>
                        <button 
                            type="button" 
                            @click="createModalOpen = false"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.store') }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1.5">Wave Label <span class="text-rose-500">*</span></label>
                                <input type="text" name="label" placeholder="e.g. H-14 Early Wave" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 mb-1.5">Days Before Due Date <span class="text-rose-500">*</span></label>
                                <input type="number" name="days_before_due" placeholder="e.g. 14, 0, or -14" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                                <p class="text-[10px] text-slate-400 mt-1">Use negative values for overdue days</p>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-2 text-xs">Active Notification Channels <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($availableChannels as $channel)
                                    <label class="inline-flex items-center text-xs p-2.5 rounded-xl border border-slate-200/70 hover:bg-slate-50 cursor-pointer transition">
                                        <input type="checkbox" name="channels[]" value="{{ $channel->value }}" checked class="rounded border-slate-300 text-slate-900 shadow-xs focus:ring-slate-500">
                                        <span class="ms-2 font-medium text-slate-700 whitespace-nowrap">{{ $channel->label() }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-900"></div>
                                <span class="ms-2 text-xs font-semibold text-slate-700 whitespace-nowrap">
                                    Active Immediately
                                </span>
                            </label>
                        </div>

                        <!-- Message Template in Modal -->
                        <div class="space-y-2">
                            <label class="block font-semibold text-slate-700 text-xs">
                                Custom Message Template (Optional)
                            </label>

                            <div class="flex flex-wrap gap-1 items-center pb-1">
                                <span class="text-[10px] text-slate-400 font-medium mr-1">Insert Tag:</span>
                                @foreach(['{sponsor_name}', '{orphan_name}', '{amount}', '{due_date}', '{status_text}', '{wave_label}', '{payment_info}', '{platform_name}'] as $tag)
                                    <button 
                                        type="button" 
                                        onclick="insertTag('new-wave-template', '{{ $tag }}')"
                                        class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-mono rounded-md transition border border-slate-200/80 cursor-pointer"
                                    >
                                        + {{ $tag }}
                                    </button>
                                @endforeach
                            </div>

                            <textarea 
                                id="new-wave-template" 
                                name="message_template" 
                                rows="4" 
                                placeholder="Leave empty to use the default reminder message format..."
                                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 font-mono text-[11px] leading-relaxed focus:border-slate-400 focus:ring-slate-400"
                            ></textarea>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                            <button 
                                type="button" 
                                @click="createModalOpen = false"
                                class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] cursor-pointer"
                            >
                                Create Wave
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
