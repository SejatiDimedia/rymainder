<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-base font-bold text-slate-900">Masuk ke Akun Anda</h2>
        <p class="text-xs text-slate-500 mt-0.5">Masukkan kredensial terdaftar untuk mengakses dashboard</p>
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
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
            <input 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autofocus 
                autocomplete="username"
                placeholder="nama@yayasan.org" 
                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400"
            />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold text-slate-700">Kata Sandi</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[11px] text-slate-500 hover:text-slate-900 transition">
                        Lupa sandi?
                    </a>
                @endif
            </div>
            <input 
                id="password" 
                type="password" 
                name="password" 
                required 
                autocomplete="current-password"
                placeholder="••••••••" 
                class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 focus:border-slate-400 focus:ring-slate-400"
            />
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
                <span class="ms-2 text-xs text-slate-600">Ingat sesi saya</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button 
                type="submit" 
                class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98]"
            >
                Masuk Sekarang
            </button>
        </div>
    </form>

    <!-- Demo Quick Login Helper -->
    <div class="mt-6 pt-5 border-t border-slate-100 text-xs">
        <div class="font-semibold text-slate-700 mb-2 flex items-center justify-between">
            <span>Akun Pengujian Demo:</span>
            <span class="text-[10px] text-slate-400 font-normal">Klik untuk mengisi</span>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <button 
                type="button" 
                onclick="fillDemo('admin@yayasan.org', 'password')"
                class="p-2.5 rounded-xl border border-slate-200/80 hover:border-slate-300 bg-slate-50/50 hover:bg-slate-100/70 text-left transition text-[11px]"
            >
                <div class="font-bold text-slate-900">Super Admin</div>
                <div class="text-slate-500 text-[10px] truncate">admin@yayasan.org</div>
            </button>

            <button 
                type="button" 
                onclick="fillDemo('staf@yayasan.org', 'password')"
                class="p-2.5 rounded-xl border border-slate-200/80 hover:border-slate-300 bg-slate-50/50 hover:bg-slate-100/70 text-left transition text-[11px]"
            >
                <div class="font-bold text-slate-900">Staf Program</div>
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
