<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Platform & Branding Settings</h1>
                <p class="text-xs text-slate-500 mt-0.5">Customize your organization name, platform branding, logo mark, and browser favicon</p>
            </div>
            <div>
                <a 
                    href="{{ route('admin.settings.index') }}" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200/90 shadow-xs transition active:scale-[0.98]"
                >
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Manage Reminder Waves
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

        <!-- Sub-Navigation Tabs: Platform & Branding | Telegram Bot | Reminder Waves -->
        <div class="flex items-center gap-1 p-1 bg-slate-200/50 rounded-xl w-fit text-xs">
            <span class="px-4 py-1.5 rounded-lg bg-white font-semibold text-slate-900 shadow-xs cursor-default">
                Platform & Branding
            </span>
            <a href="{{ route('admin.settings.telegram') }}" class="px-4 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition">
                Telegram Bot
            </a>
            <a href="{{ route('admin.settings.index') }}" class="px-4 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition">
                Reminder Waves
            </a>
        </div>

        <form 
            method="POST" 
            action="{{ route('admin.settings.platform.update') }}" 
            enctype="multipart/form-data" 
            x-data="{ 
                logoPreview: null,
                faviconPreview: null,
                previewLogo(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.logoPreview = URL.createObjectURL(file);
                    }
                },
                previewFavicon(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.faviconPreview = URL.createObjectURL(file);
                    }
                }
            }"
            class="space-y-6"
        >
            @csrf
            @method('PUT')

            <!-- 1. Platform Naming Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
                <div class="flex items-start gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 tracking-tight">Platform Identity & Naming</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Configure the display name and slogan for your organization's pledge portal.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Platform Name -->
                    <div>
                        <label for="platform_name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Platform / Organization Name <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="platform_name" 
                            name="platform_name" 
                            value="{{ old('platform_name', $platformName) }}" 
                            required 
                            placeholder="e.g. Rymainder or Yayasan Peduli Anak"
                            class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:ring-slate-400 py-2.5 px-3.5 transition"
                        />
                        <p class="text-[11px] text-slate-400 mt-1">Displayed in browser title bars, navigation bar, emails, and audit reports.</p>
                        @error('platform_name')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Platform Tagline -->
                    <div>
                        <label for="platform_tagline" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Platform Tagline / Subtitle
                        </label>
                        <input 
                            type="text" 
                            id="platform_tagline" 
                            name="platform_tagline" 
                            value="{{ old('platform_tagline', $platformTagline) }}" 
                            placeholder="e.g. Pledge Cloud Platform or Automated Donor Reminders"
                            class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:ring-slate-400 py-2.5 px-3.5 transition"
                        />
                        <p class="text-[11px] text-slate-400 mt-1">Displayed beneath the brand logo on the sidebar and login screen.</p>
                        @error('platform_tagline')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Platform Timezone -->
                    <div class="md:col-span-2 pt-4 border-t border-slate-100">
                        <label for="platform_timezone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            System Timezone (Reminder Scheduling Timezone) <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                            <div x-data="{
                                browserTz: Intl.DateTimeFormat().resolvedOptions().timeZone,
                                applyBrowserTz() {
                                    const select = document.getElementById('platform_timezone');
                                    if (!select) return;
                                    for (let opt of select.options) {
                                        if (opt.value === this.browserTz) {
                                            select.value = opt.value;
                                            return;
                                        }
                                    }
                                    const offsetHours = -new Date().getTimezoneOffset() / 60;
                                    if (offsetHours === 7) select.value = 'Asia/Jakarta';
                                    else if (offsetHours === 8) select.value = 'Asia/Makassar';
                                    else if (offsetHours === 9) select.value = 'Asia/Jayapura';
                                    else select.value = 'UTC';
                                }
                            }">
                                <select 
                                    id="platform_timezone" 
                                    name="platform_timezone" 
                                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400 py-2.5 px-3.5 transition"
                                >
                                    @foreach($supportedTimezones as $val => $label)
                                        <option value="{{ $val }}" {{ old('platform_timezone', $platformTimezone) === $val ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="mt-2 flex items-center justify-between gap-2 flex-wrap text-[11px]">
                                    <p class="text-slate-400">
                                        Detected device timezone: <strong class="text-slate-700 font-mono" x-text="browserTz"></strong>
                                    </p>
                                    <button 
                                        type="button" 
                                        @click="applyBrowserTz()" 
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2 py-0.5 rounded-lg border border-indigo-200 transition cursor-pointer"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Match My Device Timezone
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1.5 leading-relaxed">
                                    All reminder schedules (Daily, Weekly, Hourly) and delivery audit trail timestamps are evaluated according to this configured timezone.
                                </p>
                            </div>
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between gap-3 text-xs">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Current Active System Time</span>
                                    <span class="text-sm font-bold text-slate-900 font-mono mt-0.5 block">{{ $currentTime }}</span>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $currentTimezoneLabel }}
                                </span>
                            </div>
                        </div>
                        @error('platform_timezone')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 2. Brand Logo Upload Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
                <div class="flex items-start gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 border border-violet-100 text-violet-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 tracking-tight">Brand Logo Mark</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Upload your custom logo mark. Recommended format: Square (1:1), PNG, JPG, or SVG, max 2MB.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                    <!-- Left: Current Active Logo Preview -->
                    <div class="p-4 bg-slate-50/70 border border-slate-200/80 rounded-2xl flex flex-col items-center justify-center text-center">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-3">Active Logo Preview</span>
                        
                        <!-- Logo in Black Box (as displayed in sidebar) -->
                        <div class="w-20 h-20 rounded-2xl bg-black overflow-hidden flex items-center justify-center shadow-md ring-1 ring-slate-900/10 mb-3">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" alt="New Logo Preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoPreview">
                                <img src="{{ $platformLogo }}" alt="{{ $platformName }} Logo" class="w-full h-full object-cover">
                            </template>
                        </div>

                        <div class="flex items-center gap-2 mt-1">
                            @if($hasCustomLogo)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                    Custom Logo Active
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200/80 text-slate-700 border border-slate-300/60">
                                    Default System Logo
                                </span>
                            @endif
                        </div>

                        @if($hasCustomLogo)
                            <div class="mt-4 pt-3 border-t border-slate-200/80 w-full">
                                <button 
                                    type="button" 
                                    onclick="document.getElementById('form-reset-logo').submit()"
                                    class="text-xs font-semibold text-rose-600 hover:text-rose-700 transition"
                                >
                                    Reset to Default Logo
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Upload New File -->
                    <div class="lg:col-span-2 space-y-3">
                        <label class="block text-xs font-semibold text-slate-700">
                            Select New Logo File
                        </label>
                        
                        <div class="border-2 border-dashed border-slate-200 hover:border-slate-400 rounded-2xl p-6 text-center transition bg-slate-50/30 hover:bg-slate-50/60">
                            <svg class="w-8 h-8 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <p class="text-xs font-semibold text-slate-700">Click to browse or drag and drop image</p>
                            <p class="text-[11px] text-slate-400 mt-1">PNG, JPG, JPEG, SVG, WebP (up to 2MB). Ideal dimensions: 512x512px.</p>
                            <input 
                                type="file" 
                                name="logo" 
                                id="logo" 
                                accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp"
                                @change="previewLogo($event)"
                                class="mt-3 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer"
                            />
                        </div>
                        @error('logo')
                            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 3. Favicon Settings Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-5">
                <div class="flex items-start gap-3 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 tracking-tight">Browser Tab Favicon (Optional)</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Customize the icon shown on browser tabs. If not specified, your brand logo is automatically used.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                    <div class="p-4 bg-slate-50/70 border border-slate-200/80 rounded-2xl flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-black overflow-hidden flex items-center justify-center shrink-0 shadow-xs">
                            <template x-if="faviconPreview">
                                <img :src="faviconPreview" alt="Favicon Preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!faviconPreview">
                                <img src="{{ $platformFavicon }}" alt="Favicon" class="w-full h-full object-cover">
                            </template>
                        </div>
                        <div class="min-w-0">
                            <span class="block text-xs font-semibold text-slate-800 truncate">{{ $platformName }}</span>
                            <span class="block text-[11px] text-slate-400">Tab preview icon</span>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <input 
                            type="file" 
                            name="favicon" 
                            id="favicon" 
                            accept="image/png,image/x-icon,image/svg+xml"
                            @change="previewFavicon($event)"
                            class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200 cursor-pointer"
                        />
                        <p class="text-[11px] text-slate-400 mt-1">PNG, ICO, or SVG format (max 1MB). Recommended size: 64x64px.</p>
                        @error('favicon')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Controls -->
            <div class="pt-2 flex items-center justify-end gap-3">
                <a 
                    href="{{ route('dashboard') }}" 
                    class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 shadow-xs transition active:scale-[0.98]"
                >
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl shadow-xs transition active:scale-[0.98]"
                >
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Branding Settings
                </button>
            </div>
        </form>

        <!-- Hidden Reset Logo Form -->
        <form id="form-reset-logo" method="POST" action="{{ route('admin.settings.platform.reset-logo') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </div>
</x-app-layout>
