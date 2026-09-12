<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-base font-bold text-slate-900">Sign in to your account</h2>
        <p class="text-xs text-slate-500 mt-0.5">Enter your registered credentials to access the admin portal</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($errors->any())
        <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    autocomplete="username"
                    placeholder="name@yayasan.org" 
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400 py-2.5 pl-10 pr-3.5 transition"
                />
            </div>
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold text-slate-700">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[11px] text-indigo-600 hover:text-indigo-700 font-medium transition">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                    placeholder="••••••••" 
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400 py-2.5 pl-10 pr-3.5 transition"
                />
            </div>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    name="remember" 
                    class="rounded border-slate-300 text-slate-900 shadow-xs focus:ring-slate-500"
                >
                <span class="ms-2 text-xs text-slate-600">Remember me on this device</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button 
                type="submit" 
                class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] flex items-center justify-center gap-2"
            >
                <span>Sign in to Dashboard</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </form>

    <!-- Demo Quick Login Helper -->
    <div class="mt-6 pt-5 border-t border-slate-100 text-xs">
        <div class="font-semibold text-slate-700 mb-2 flex items-center justify-between">
            <span>Quick Demo Login:</span>
            <span class="text-[10px] text-slate-400 font-normal">Click to auto-fill</span>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <button 
                type="button" 
                onclick="fillDemo('admin@yayasan.org', 'password')"
                class="p-2.5 rounded-xl border border-slate-200/80 hover:border-slate-300 bg-slate-50/50 hover:bg-slate-100/70 text-left transition text-[11px] group"
            >
                <div class="font-bold text-slate-900 group-hover:text-indigo-600 transition">Super Admin</div>
                <div class="text-slate-500 text-[10px] truncate">admin@yayasan.org</div>
            </button>

            <button 
                type="button" 
                onclick="fillDemo('staf@yayasan.org', 'password')"
                class="p-2.5 rounded-xl border border-slate-200/80 hover:border-slate-300 bg-slate-50/50 hover:bg-slate-100/70 text-left transition text-[11px] group"
            >
                <div class="font-bold text-slate-900 group-hover:text-indigo-600 transition">Program Staff</div>
                <div class="text-slate-500 text-[10px] truncate">staf@yayasan.org</div>
            </button>
        </div>
    </div>

    <script>
        function fillDemo(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</x-guest-layout>
