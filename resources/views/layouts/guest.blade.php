<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Rymainder') }} - Authentication</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

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
                <a href="/" class="flex items-center gap-3.5 group">
                    <div class="w-12 h-12 rounded-2xl overflow-hidden shadow-md ring-1 ring-slate-900/10 group-hover:scale-105 transition-transform duration-200 shrink-0 bg-black">
                        <img src="{{ asset('images/logo.png') }}" alt="Rymainder Logo" class="w-full h-full object-cover">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-900 tracking-tight text-2xl leading-none">Rymainder</span>
                        <span class="text-[11px] font-semibold text-indigo-600 tracking-wider uppercase mt-1">Pledge Cloud Platform</span>
                    </div>
                </a>
                <p class="text-xs text-slate-500 mt-2.5 text-center max-w-xs">
                    Automated donor pledge reminders & multi-channel cycle audits
                </p>
            </div>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
            <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-xs">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} Rymainder. All rights reserved.
            </div>
        </div>
    </body>
</html>
