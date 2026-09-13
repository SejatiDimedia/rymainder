<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Sponsor: {{ $sponsor->name }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Update donor profile, payment cycle, or active status</p>
            </div>
            <a href="{{ route('admin.sponsors.show', $sponsor) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 transition">
                ← Back to Details
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200/80 shadow-xs">

            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs">
                    <div class="font-semibold mb-1">Please correct the following errors:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.sponsors.update', $sponsor) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Section: Sponsor Identity -->
                <div class="border-b border-slate-100 pb-5">
                    <h2 class="text-sm font-bold text-slate-900 mb-4">1. Sponsor Identity & Contact Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Name -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $sponsor->name) }}" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $sponsor->email) }}" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                        </div>

                        <!-- Phone (WhatsApp) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">WhatsApp / Mobile Number <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone', $sponsor->phone) }}" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                        </div>

                        <!-- Orphan Name -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Beneficiary / Child Name (Optional)</label>
                            <input type="text" name="orphan_name" value="{{ old('orphan_name', $sponsor->orphan_name) }}" placeholder="Leave blank if general program" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                        </div>

                        <!-- Telegram Chat ID -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Telegram Chat ID (Optional / Manual Link)</label>
                            <input type="text" name="telegram_chat_id" value="{{ old('telegram_chat_id', $sponsor->telegram_chat_id) }}" placeholder="e.g. 123456789" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400 font-mono">
                            <p class="text-[11px] text-slate-400 mt-1">Donor Telegram Chat ID. Can be entered manually for direct testing without webhooks.</p>
                        </div>
                    </div>
                </div>

                <!-- Section: Commitment Parameters -->
                <div class="border-b border-slate-100 pb-5">
                    <h2 class="text-sm font-bold text-slate-900 mb-4">2. Commitment & Due Date Calculation</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Last Donation Date -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Last Donation Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="last_donation_date" value="{{ old('last_donation_date', $sponsor->last_donation_date->toDateString()) }}" max="{{ now()->toDateString() }}" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                        </div>

                        <!-- Frequency -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Payment Frequency <span class="text-rose-500">*</span></label>
                            <select name="frequency" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                                @foreach($frequencies as $freq)
                                    <option value="{{ $freq->value }}" {{ old('frequency', $sponsor->frequency->value) === $freq->value ? 'selected' : '' }}>
                                        {{ $freq->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Donation Amount (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="amount" value="{{ old('amount', $sponsor->amount) }}" min="0" step="10000" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sponsor Status <span class="text-rose-500">*</span></label>
                            <select name="status" required class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status', $sponsor->status->value) === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Channel Preferences -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Allowed Notification Channels</label>
                            <div class="mt-2 flex flex-wrap gap-4">
                                @foreach($availableChannels as $channel)
                                    <label class="inline-flex items-center text-xs text-slate-700 cursor-pointer">
                                        <input type="checkbox" name="channel_preferences[]" value="{{ $channel->value }}" 
                                            {{ in_array($channel->value, old('channel_preferences', $sponsor->channel_preferences ?? [])) ? 'checked' : '' }}
                                            class="rounded border-slate-300 text-slate-900 shadow-xs focus:ring-slate-500">
                                        <span class="ms-2 font-medium">{{ $channel->label() }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Notes -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Additional Notes (Optional)</label>
                    <textarea name="notes" rows="3" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400">{{ old('notes', $sponsor->notes) }}</textarea>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <div>
                        <!-- Delete Sponsor Form -->
                        <button 
                            type="button" 
                            onclick="if(confirm('Are you sure you want to delete this sponsor record? This action cannot be undone.')) { document.getElementById('delete-sponsor-form').submit(); }"
                            class="text-xs text-rose-600 hover:text-rose-700 hover:underline font-medium"
                        >
                            Delete Sponsor
                        </button>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.sponsors.show', $sponsor) }}" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-xl text-xs font-medium hover:bg-slate-50 transition shadow-xs">
                            Cancel
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl text-xs shadow-xs transition active:scale-[0.98]">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-sponsor-form" method="POST" action="{{ route('admin.sponsors.destroy', $sponsor) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>

        </div>
    </div>
</x-app-layout>
