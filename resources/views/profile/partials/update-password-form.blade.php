<section class="h-full flex flex-col justify-between">
    <div>
        <!-- Section Header -->
        <div class="flex items-start gap-3 pb-5 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100/80 flex items-center justify-center text-amber-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 tracking-tight">Security & Password</h3>
                <p class="text-xs text-slate-500 mt-0.5">Ensure your account is protected with a strong, random password.</p>
            </div>
        </div>

        <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-4">
            @csrf
            @method('put')

            <!-- Current Password -->
            <div>
                <label for="update_password_current_password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Current Password <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <input 
                        id="update_password_current_password" 
                        name="current_password" 
                        type="password" 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:ring-slate-400 py-2.5 pl-10 pr-3.5 transition" 
                        autocomplete="current-password" 
                        placeholder="••••••••••••"
                    />
                </div>
                @if($errors->updatePassword->get('current_password'))
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $errors->updatePassword->get('current_password')[0] }}</p>
                @endif
            </div>

            <!-- New Password -->
            <div>
                <label for="update_password_password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    New Password <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <input 
                        id="update_password_password" 
                        name="password" 
                        type="password" 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:ring-slate-400 py-2.5 pl-10 pr-3.5 transition" 
                        autocomplete="new-password" 
                        placeholder="••••••••••••"
                    />
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Minimum 8 characters with numbers and symbols recommended.</p>
                @if($errors->updatePassword->get('password'))
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $errors->updatePassword->get('password')[0] }}</p>
                @endif
            </div>

            <!-- Confirm New Password -->
            <div>
                <label for="update_password_password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Confirm New Password <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <input 
                        id="update_password_password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:ring-slate-400 py-2.5 pl-10 pr-3.5 transition" 
                        autocomplete="new-password" 
                        placeholder="••••••••••••"
                    />
                </div>
                @if($errors->updatePassword->get('password_confirmation'))
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $errors->updatePassword->get('password_confirmation')[0] }}</p>
                @endif
            </div>

            <!-- Submit Section -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-4">
                <button 
                    type="submit" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl shadow-xs transition active:scale-[0.98] whitespace-nowrap"
                >
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Update Password
                </button>

                @if (session('status') === 'password-updated')
                    <div 
                        x-data="{ show: true }" 
                        x-show="show" 
                        x-transition 
                        x-init="setTimeout(() => show = false, 3000)" 
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl whitespace-nowrap"
                    >
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Password updated.
                    </div>
                @endif
            </div>
        </form>
    </div>
</section>
