<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Account Settings</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage personal credentials, profile identity, and account security preferences</p>
            </div>
            <div class="flex items-center gap-2">
                <a 
                    href="{{ route('dashboard') }}" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200/90 shadow-xs transition active:scale-[0.98]"
                >
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        <!-- 1. Top Profile Hero / Identity Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <!-- Large Avatar / Monogram -->
                <div class="relative">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-slate-900 to-slate-700 text-white flex items-center justify-center font-bold text-xl shadow-xs ring-4 ring-slate-100/80">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-white" title="Active Account"></span>
                </div>

                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-lg font-bold text-slate-900 tracking-tight">{{ $user->name }}</h2>
                        @if($user->role->value === 'super_admin')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-violet-50 text-violet-700 border border-violet-200/70 whitespace-nowrap">
                                <svg class="w-3 h-3 text-violet-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
                                Super Admin
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap">
                                Program Staff
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $user->email }}
                    </p>
                </div>
            </div>

            <!-- Quick Account Metadata Pills -->
            <div class="flex items-center gap-3 sm:gap-6 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6 text-xs">
                <div>
                    <span class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider">Member Since</span>
                    <span class="font-semibold text-slate-800 mt-0.5 block whitespace-nowrap">
                        {{ $user->created_at ? $user->created_at->format('M d, Y') : 'Active' }}
                    </span>
                </div>
                <div class="w-px h-8 bg-slate-200/80"></div>
                <div>
                    <span class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider">Timezone</span>
                    <span class="font-semibold text-slate-800 mt-0.5 block whitespace-nowrap">Asia/Jakarta (WIB)</span>
                </div>
                <div class="w-px h-8 bg-slate-200/80"></div>
                <div>
                    <span class="block text-[11px] font-medium text-slate-400 uppercase tracking-wider">Security</span>
                    <span class="font-semibold text-emerald-600 mt-0.5 flex items-center gap-1 whitespace-nowrap">
                        <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Standard
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Dual Column Layout: Profile Information & Password Security -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left: Profile Details -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- Right: Password & Authentication -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- 3. Bottom Danger Zone Card -->
        <div class="bg-white rounded-2xl border border-rose-200/80 shadow-xs overflow-hidden">
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</x-app-layout>
