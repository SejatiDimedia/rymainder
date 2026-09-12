<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Rymainder') }} - Autentikasi</title>

        <!-- Google / Bunny Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#f8f9fc] text-slate-800 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Brand Logo matching reference -->
            <div class="flex flex-col items-center justify-center">
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-sm group-hover:bg-slate-800 transition">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                    </div>
                    <span class="font-bold text-slate-900 tracking-tight text-xl">Rymainder</span>
                </a>
                <p class="text-xs text-slate-500 mt-2 text-center">
                    Sistem Otomasi Pengingat Donasi & Pemantauan Sponsor
                </p>
            </div>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
            <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-xs">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} Rymainder. Dilindungi hak cipta.
            </div>
        </div>
    </body>
</html>
