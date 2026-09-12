<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
                    Pengaturan Gelombang Reminder
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Kelola aturan jadwal pengiriman (H-7, H-3, Hari-H, Overdue) dan channel yang aktif tanpa deploy kode.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Info Guide -->
            <div class="p-4 bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-800 rounded-xl text-teal-900 dark:text-teal-200 text-xs leading-relaxed">
                <strong>Panduan Aturan Gelombang (Rules Engine):</strong><br>
                • Nilai <strong>Hari sebelum jatuh tempo positif (contoh: 7)</strong> berarti pengingat dikirim pada H-7 sebelum jatuh tempo.<br>
                • Nilai <strong>0</strong> berarti pengingat dikirim tepat pada hari jatuh tempo (Hari-H).<br>
                • Nilai <strong>negatif (contoh: -7)</strong> berarti pengingat keterlambatan (Overdue) dikirim 7 hari setelah jatuh tempo lewat.<br>
                • Channel WhatsApp dapat dibatasi hanya untuk wave kritis (H-3, H-0, Overdue) guna menekan biaya Meta.
            </div>

            <!-- Waves Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($settings as $setting)
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between">
                        <form method="POST" action="{{ route('admin.settings.update', $setting) }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                                <h3 class="font-bold text-gray-900 dark:text-white text-base">
                                    {{ $setting->label }}
                                </h3>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" {{ $setting->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-teal-600"></div>
                                    <span class="ms-2 text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        {{ $setting->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Label Gelombang</label>
                                    <input type="text" name="label" value="{{ $setting->label }}" required class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                                </div>

                                <div>
                                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Hari Sebelum Tempo</label>
                                    <input type="number" name="days_before_due" value="{{ $setting->days_before_due }}" required class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500">
                                    <p class="text-[10px] text-gray-400 mt-1">Gunakan minus (-) untuk overdue</p>
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-2 text-xs">Channel Pengiriman yang Diaktifkan</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($availableChannels as $channel)
                                        <label class="inline-flex items-center text-xs p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                                            <input type="checkbox" name="channels[]" value="{{ $channel->value }}" 
                                                {{ $setting->hasChannel($channel) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-teal-600 shadow-sm focus:ring-teal-500">
                                            <span class="ms-2 font-medium text-gray-700 dark:text-gray-200">{{ $channel->label() }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg text-xs font-semibold shadow-sm transition">
                                    Simpan Perubahan Wave
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
