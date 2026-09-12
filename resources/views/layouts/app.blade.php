<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Rymainder') }}</title>

        <!-- Google / Bunny Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#f8f9fc] text-slate-800 h-full">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">
            <!-- Left Sidebar Navigation -->
            @include('layouts.sidebar')

            <!-- Main Content Canvas -->
            <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
                <!-- Top Header Bar -->
                <header class="sticky top-0 z-30 bg-[#f8f9fc]/90 backdrop-blur-md border-b border-slate-200/70 px-4 sm:px-8 py-3.5 flex items-center justify-between">
                    <!-- Left: Mobile Menu Trigger + Page Title -->
                    <div class="flex items-center gap-3">
                        <button 
                            type="button" 
                            @click="sidebarOpen = !sidebarOpen" 
                            class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition"
                            aria-label="Toggle Navigation"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <div>
                            @isset($header)
                                {{ $header }}
                            @else
                                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
                            @endisset
                        </div>
                    </div>

                    <!-- Right Controls: Notification Bell, More Options, Profile Chip -->
                    <div class="flex items-center gap-2.5">
                        <!-- Notifications Pill Button -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open" 
                                type="button" 
                                class="w-9 h-9 rounded-full border border-slate-200/90 bg-white flex items-center justify-center text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-xs focus:outline-none"
                                title="Notifikasi Sistem"
                            >
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                </svg>
                                <!-- Ping Dot -->
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-blue-600 rounded-full ring-2 ring-white"></span>
                            </button>

                            <div 
                                x-show="open" 
                                @click.outside="open = false" 
                                x-cloak 
                                class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-lg border border-slate-100 p-3 z-50 text-xs"
                            >
                                <div class="font-semibold text-slate-900 pb-2 mb-2 border-b border-slate-100 flex items-center justify-between">
                                    <span>Notifikasi Sistem</span>
                                    <span class="text-[10px] text-emerald-600 font-medium">Scheduler Aktif</span>
                                </div>
                                <p class="text-slate-600">
                                    Pengingat otomatis dijadwalkan setiap hari pukul <strong>08:00 WIB</strong> untuk seluruh sponsor aktif.
                                </p>
                            </div>
                        </div>

                        <!-- More Options Button (...) -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open" 
                                type="button" 
                                class="w-9 h-9 rounded-full border border-slate-200/90 bg-white flex items-center justify-center text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-xs focus:outline-none"
                                title="Opsi Cepat"
                            >
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="19" cy="12" r="1"></circle>
                                    <circle cx="5" cy="12" r="1"></circle>
                                </svg>
                            </button>

                            <div 
                                x-show="open" 
                                @click.outside="open = false" 
                                x-cloak 
                                class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-lg border border-slate-100 py-1.5 z-50 text-xs"
                            >
                                <a href="{{ route('admin.sponsors.create') }}" class="block px-3 py-2 text-slate-700 hover:bg-slate-50">
                                    + Tambah Sponsor Baru
                                </a>
                                @if(Auth::user()->isSuperAdmin())
                                    <a href="{{ route('admin.settings.index') }}" class="block px-3 py-2 text-slate-700 hover:bg-slate-50">
                                        Atur Gelombang Reminder
                                    </a>
                                @endif
                                <a href="{{ route('admin.logs.index') }}" class="block px-3 py-2 text-slate-700 hover:bg-slate-50">
                                    Lihat Semua Log Audit
                                </a>
                            </div>
                        </div>

                        <!-- User Profile Chip -->
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center gap-2 pl-2 pr-3 py-1 rounded-full border border-slate-200/90 bg-white hover:bg-slate-50 transition shadow-xs text-xs font-medium text-slate-700">
                                    <div class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <span class="max-w-[100px] truncate hidden sm:inline">{{ Auth::user()->name }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-2 border-b border-slate-100 text-xs">
                                    <div class="font-semibold text-slate-800">{{ Auth::user()->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ Auth::user()->email }}</div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ Auth::user()->isSuperAdmin() ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ Auth::user()->role?->label() ?? 'Staf' }}
                                        </span>
                                    </div>
                                </div>

                                <x-dropdown-link :href="route('profile.edit')" class="text-xs">
                                    {{ __('Profil Pengguna') }}
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-xs text-rose-600 hover:text-rose-700">
                                        {{ __('Keluar (Log Out)') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <!-- Main Content Area -->
                <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
