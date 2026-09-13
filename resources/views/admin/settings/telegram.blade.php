<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Telegram Bot Message Templates</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kustomisasi balasan otomatis, pesan selamat datang, dan konfirmasi aktivasi bot Telegram</p>
            </div>
            <div class="flex items-center gap-2">
                @if($botUsername)
                    <a 
                        href="https://t.me/{{ $botUsername }}" 
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-semibold rounded-xl border border-sky-200 transition"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.93-1.28 4.88-2.12 5.86-2.54 2.8-.19 3.38 1.15 3.39 1.47z"/></svg>
                        <span>@ {{ $botUsername }}</span>
                    </a>
                @endif
                <a 
                    href="{{ route('admin.settings.index') }}" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200/90 shadow-xs transition active:scale-[0.98]"
                >
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Reminder Waves</span>
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

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs space-y-1 shadow-xs">
                <div class="font-semibold">Mohon periksa kembali form berikut:</div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Sub-Navigation Tabs: Platform Branding | Bot Telegram | Reminder Waves -->
        <div class="flex items-center gap-1 p-1 bg-slate-200/50 rounded-xl w-fit text-xs">
            <a href="{{ route('admin.settings.platform') }}" class="px-4 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition">
                Platform & Branding
            </a>
            <span class="px-4 py-1.5 rounded-lg bg-white font-semibold text-slate-900 shadow-xs cursor-default">
                Template Bot Telegram
            </span>
            <a href="{{ route('admin.settings.index') }}" class="px-4 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 font-medium transition">
                Reminder Waves
            </a>
        </div>

        <form method="POST" action="{{ route('admin.settings.telegram.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Grid 1: Aktivasi Berhasil & Selamat Datang -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- 1. Pesan Aktivasi Berhasil -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between" x-data="{ text: @js(old('telegram_msg_activation_success', $activationSuccessMsg)) }">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                                    ✓
                                </div>
                                <h3 class="font-bold text-slate-900 text-sm">Pesan Berhasil Terhubung</h3>
                            </div>
                            <span class="text-[11px] text-slate-400 font-mono" x-text="text.length + ' / 2000'"></span>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">
                            Dikirimkan saat sponsor mengklik tautan aktivasi dan menekan tombol <strong>START</strong> di Telegram.
                        </p>

                        <!-- Variable Chips -->
                        <div class="mb-3 flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] text-slate-400">Variabel Dinamis:</span>
                            <button type="button" @click="text += ' {sponsor_name}'" class="text-[10px] px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono transition cursor-pointer">
                                {sponsor_name}
                            </button>
                            <button type="button" @click="text += ' {platform_name}'" class="text-[10px] px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono transition cursor-pointer">
                                {platform_name}
                            </button>
                        </div>

                        <textarea 
                            name="telegram_msg_activation_success" 
                            rows="7" 
                            x-model="text"
                            required
                            maxlength="2000"
                            class="w-full text-xs font-mono rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 leading-relaxed focus:border-slate-400 focus:ring-slate-400"
                        ></textarea>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Mendukung format Markdown standar Telegram (*tebal*, _miring_).</p>
                </div>

                <!-- 2. Pesan Selamat Datang (/start tanpa kode) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between" x-data="{ text: @js(old('telegram_msg_welcome', $welcomeMsg)) }">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-xs">
                                    👋
                                </div>
                                <h3 class="font-bold text-slate-900 text-sm">Pesan Sambutan (/start)</h3>
                            </div>
                            <span class="text-[11px] text-slate-400 font-mono" x-text="text.length + ' / 2000'"></span>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">
                            Dikirimkan saat pengguna membuka bot langsung tanpa kode tautan onboarding sponsor.
                        </p>

                        <!-- Variable Chips -->
                        <div class="mb-3 flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] text-slate-400">Variabel Dinamis:</span>
                            <button type="button" @click="text += ' {platform_name}'" class="text-[10px] px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono transition cursor-pointer">
                                {platform_name}
                            </button>
                        </div>

                        <textarea 
                            name="telegram_msg_welcome" 
                            rows="7" 
                            x-model="text"
                            required
                            maxlength="2000"
                            class="w-full text-xs font-mono rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 leading-relaxed focus:border-slate-400 focus:ring-slate-400"
                        ></textarea>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Mendukung format Markdown standar Telegram.</p>
                </div>

            </div>

            <!-- Grid 2: Balasan Chat Lain & Kode Invalid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- 3. Balasan Otomatis Chat Lain (Default Reply) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between" x-data="{ text: @js(old('telegram_msg_default_reply', $defaultReplyMsg)) }">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">
                                    💬
                                </div>
                                <h3 class="font-bold text-slate-900 text-sm">Balasan Otomatis Chat Masuk Lainnya</h3>
                            </div>
                            <span class="text-[11px] text-slate-400 font-mono" x-text="text.length + ' / 2000'"></span>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">
                            Balasan standar saat pengguna mengetik pesan teks bebas apa saja di luar perintah sistem.
                        </p>

                        <!-- Variable Chips -->
                        <div class="mb-3 flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] text-slate-400">Variabel Dinamis:</span>
                            <button type="button" @click="text += ' {platform_name}'" class="text-[10px] px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono transition cursor-pointer">
                                {platform_name}
                            </button>
                        </div>

                        <textarea 
                            name="telegram_msg_default_reply" 
                            rows="5" 
                            x-model="text"
                            required
                            maxlength="2000"
                            class="w-full text-xs font-mono rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 leading-relaxed focus:border-slate-400 focus:ring-slate-400"
                        ></textarea>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Dapat diisi arahan menghubungi admin/WhatsApp resmi yayasan.</p>
                </div>

                <!-- 4. Pesan Kode Tidak Dikenali / Invalid -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between" x-data="{ text: @js(old('telegram_msg_invalid_code', $invalidCodeMsg)) }">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xs">
                                    ✕
                                </div>
                                <h3 class="font-bold text-slate-900 text-sm">Pesan Kode Aktivasi Tidak Valid</h3>
                            </div>
                            <span class="text-[11px] text-slate-400 font-mono" x-text="text.length + ' / 2000'"></span>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">
                            Dikirimkan saat kode aktivasi sponsor yang dikirim tidak ditemukan di database atau salah ketik.
                        </p>

                        <!-- Variable Chips -->
                        <div class="mb-3 flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] text-slate-400">Variabel Dinamis:</span>
                            <button type="button" @click="text += ' {platform_name}'" class="text-[10px] px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono transition cursor-pointer">
                                {platform_name}
                            </button>
                        </div>

                        <textarea 
                            name="telegram_msg_invalid_code" 
                            rows="5" 
                            x-model="text"
                            required
                            maxlength="2000"
                            class="w-full text-xs font-mono rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 leading-relaxed focus:border-slate-400 focus:ring-slate-400"
                        ></textarea>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">Memberi tahu pengguna untuk meminta link baru dari staf.</p>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-6 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
                <div>
                    <button 
                        type="button"
                        onclick="if(confirm('Apakah Anda yakin ingin mengembalikan seluruh template pesan Bot Telegram ke teks default bawaan?')) { document.getElementById('reset-templates-form').submit(); }"
                        class="text-xs text-rose-600 hover:text-rose-800 hover:underline font-medium transition cursor-pointer"
                    >
                        Reset ke Format Default
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition active:scale-[0.98] flex items-center gap-2 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Perubahan Template</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- Hidden Reset Form -->
        <form id="reset-templates-form" method="POST" action="{{ route('admin.settings.telegram.reset') }}" class="hidden">
            @csrf
        </form>

    </div>
</x-app-layout>
