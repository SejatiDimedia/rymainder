<section class="h-full flex flex-col justify-between">
    <div>
        <!-- Section Header -->
        <div class="flex items-start gap-3 pb-5 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100/80 flex items-center justify-center text-indigo-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 tracking-tight">Profile Details</h3>
                <p class="text-xs text-slate-500 mt-0.5">Update your display name and email address for system notices.</p>
            </div>
        </div>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
            @csrf
            @method('patch')

            <!-- Full Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Full Name <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <input 
                        id="name" 
                        name="name" 
                        type="text" 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:ring-slate-400 py-2.5 pl-10 pr-3.5 transition" 
                        value="{{ old('name', $user->name) }}" 
                        required 
                        autofocus 
                        autocomplete="name" 
                        placeholder="Your full name"
                    />
                </div>
                @if($errors->get('name'))
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $errors->get('name')[0] }}</p>
                @endif
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Email Address <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 placeholder:text-slate-400 focus:border-slate-400 focus:ring-slate-400 py-2.5 pl-10 pr-3.5 transition" 
                        value="{{ old('email', $user->email) }}" 
                        required 
                        autocomplete="username" 
                        placeholder="you@domain.org"
                    />
                </div>
                @if($errors->get('email'))
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $errors->get('email')[0] }}</p>
                @endif

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span>Your email address is currently unverified.</span>
                        </div>
                        <button form="send-verification" class="text-xs font-semibold text-amber-900 underline hover:text-amber-950 whitespace-nowrap">
                            Resend Link
                        </button>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-xs font-medium text-emerald-600">
                            A fresh verification link has been dispatched to your inbox.
                        </p>
                    @endif
                @endif
            </div>

            <!-- Role (Read Only Display) -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Account Role & Permissions
                </label>
                <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $user->role->value === 'super_admin' ? 'bg-violet-500' : 'bg-slate-400' }}"></span>
                        <span class="font-semibold text-slate-800">
                            {{ $user->role->value === 'super_admin' ? 'Super Administrator' : 'Program Staff' }}
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-400">Managed by System Policy</span>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-4">
                <button 
                    type="submit" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl shadow-xs transition active:scale-[0.98] whitespace-nowrap"
                >
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>

                @if (session('status') === 'profile-updated')
                    <div 
                        x-data="{ show: true }" 
                        x-show="show" 
                        x-transition 
                        x-init="setTimeout(() => show = false, 3000)" 
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl whitespace-nowrap"
                    >
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Saved successfully.
                    </div>
                @endif
            </div>
        </form>
    </div>
</section>
