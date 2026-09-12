<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Pengaturan Profil Pengguna</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola identitas akun admin, kredensial sandi, dan sesi aktif</p>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="p-6 sm:p-8 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-6 sm:p-8 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-6 sm:p-8 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
