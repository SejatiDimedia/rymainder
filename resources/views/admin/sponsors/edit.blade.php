<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
                    Edit Data Sponsor: {{ $sponsor->name }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Perbarui profil, siklus pembayaran, atau status keaktifan donatur.
                </p>
            </div>
            <a href="{{ route('admin.sponsors.show', $sponsor) }}" class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:underline">
                ← Kembali ke Detail
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 rounded-lg text-sm">
                        <div class="font-semibold mb-1">Terdapat kesalahan pengisian formulir:</div>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.sponsors.update', $sponsor) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Section: Identitas Sponsor -->
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-5">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">1. Identitas & Kontak Sponsor</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap Sponsor <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $sponsor->name) }}" required class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $sponsor->email) }}" required class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                            </div>

                            <!-- Phone (WhatsApp) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor WhatsApp / HP <span class="text-rose-500">*</span></label>
                                <input type="text" name="phone" value="{{ old('phone', $sponsor->phone) }}" required class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                            </div>

                            <!-- Orphan Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Anak Asuh (Opsional)</label>
                                <input type="text" name="orphan_name" value="{{ old('orphan_name', $sponsor->orphan_name) }}" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Parameter Donasi & Siklus -->
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-5">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">2. Komitmen Donasi & Perhitungan Jatuh Tempo</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <!-- Last Donation Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Donasi Terakhir <span class="text-rose-500">*</span></label>
                                <input type="date" name="last_donation_date" value="{{ old('last_donation_date', $sponsor->last_donation_date->toDateString()) }}" max="{{ now()->toDateString() }}" required class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                            </div>

                            <!-- Frequency -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Frekuensi Pembayaran <span class="text-rose-500">*</span></label>
                                <select name="frequency" required class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                                    @foreach($frequencies as $freq)
                                        <option value="{{ $freq->value }}" {{ old('frequency', $sponsor->frequency->value) === $freq->value ? 'selected' : '' }}>
                                            {{ $freq->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nominal Donasi (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="amount" value="{{ old('amount', (int)$sponsor->amount) }}" min="0" step="10000" required class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status Keaktifan Sponsor <span class="text-rose-500">*</span></label>
                                <select name="status" required class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->value }}" {{ old('status', $sponsor->status->value) === $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Channel Preferences -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Channel Notifikasi yang Diizinkan</label>
                                <div class="mt-2 flex flex-wrap gap-4">
                                    @foreach($availableChannels as $channel)
                                        <label class="inline-flex items-center text-sm text-gray-700 dark:text-gray-300">
                                            <input type="checkbox" name="channel_preferences[]" value="{{ $channel->value }}" 
                                                {{ in_array($channel->value, old('channel_preferences', $sponsor->channel_preferences ?? [])) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-teal-600 shadow-sm focus:ring-teal-500">
                                            <span class="ms-2 font-medium">{{ $channel->label() }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Catatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="3" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">{{ old('notes', $sponsor->notes) }}</textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" onclick="if(confirm('Apakah Anda yakin ingin menghapus data sponsor ini?')) { document.getElementById('delete-sponsor-form').submit(); }" class="text-rose-600 hover:text-rose-700 text-sm font-semibold">
                            Hapus Sponsor
                        </button>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.sponsors.show', $sponsor) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                Batal
                            </a>
                            <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-medium rounded-lg text-sm shadow-sm transition">
                                Simpan Perubahan
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
    </div>
</x-app-layout>
