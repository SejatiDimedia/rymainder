<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Rymainder') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <!-- Google / Bunny Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#f8f9fc] text-slate-800 h-screen overflow-hidden">
        <div 
            x-data="{ 
                sidebarOpen: false, 
                sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true' 
            }" 
            class="h-screen w-full flex overflow-hidden"
        >
            <!-- Left Sidebar Navigation (Supports Resize / Collapse & Fixed Position) -->
            @include('layouts.sidebar')

            <!-- Main Content Canvas (Expanded Full-Width Panel, Independently Scrollable) -->
            <div class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
                <!-- Top Header Bar -->
                <header class="sticky top-0 z-30 bg-[#f8f9fc]/90 backdrop-blur-md border-b border-slate-200/70 px-4 sm:px-8 lg:px-10 py-3.5 flex items-center justify-between">
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
                                class="w-9 h-9 rounded-full border border-slate-200 bg-white flex items-center justify-center text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-xs focus:outline-none"
                                title="System Notifications"
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
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200/90 p-3.5 z-50 text-xs"
                            >
                                <div class="font-bold text-slate-900 pb-2 mb-2 border-b border-slate-100 flex items-center justify-between">
                                    <span>System Notification</span>
                                    <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">Active</span>
                                </div>
                                <p class="text-slate-600 leading-relaxed">
                                    Automated reminders are scheduled daily at <strong>08:00 WIB</strong> for all verified active sponsors.
                                </p>
                            </div>
                        </div>

                        <!-- More Options Button (...) -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open" 
                                type="button" 
                                class="w-9 h-9 rounded-full border border-slate-200 bg-white flex items-center justify-center text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-xs focus:outline-none"
                                title="Quick Actions"
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
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-200/90 p-1.5 z-50 text-xs"
                            >
                                <a href="{{ route('admin.sponsors.create') }}" class="block px-3 py-2 text-slate-700 hover:text-slate-900 hover:bg-slate-50 rounded-xl transition font-medium">
                                    + Add New Sponsor
                                </a>
                                @if(Auth::user()->isSuperAdmin())
                                    <a href="{{ route('admin.settings.index') }}" class="block px-3 py-2 text-slate-700 hover:text-slate-900 hover:bg-slate-50 rounded-xl transition font-medium">
                                        Configure Waves
                                    </a>
                                @endif
                                <a href="{{ route('admin.logs.index') }}" class="block px-3 py-2 text-slate-700 hover:text-slate-900 hover:bg-slate-50 rounded-xl transition font-medium">
                                    View Audit Logs
                                </a>
                            </div>
                        </div>

                        <!-- User Profile Chip & Harmonious Dropdown -->
                        <div class="relative" x-data="{ profileOpen: false }">
                            <button 
                                @click="profileOpen = !profileOpen" 
                                type="button" 
                                class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition shadow-xs text-xs font-medium text-slate-800 focus:outline-none"
                            >
                                <div class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold shrink-0">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="max-w-[120px] truncate hidden sm:inline font-semibold text-slate-800">{{ Auth::user()->name }}</span>
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Profile Dropdown Menu (Harmonious White/Slate Colors) -->
                            <div 
                                x-show="profileOpen" 
                                @click.outside="profileOpen = false" 
                                x-cloak 
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-200/90 p-2 z-50 text-xs"
                            >
                                <!-- User Identity Banner -->
                                <div class="p-3 bg-slate-50 rounded-xl mb-1 border border-slate-100">
                                    <div class="font-bold text-slate-900 text-xs truncate">{{ Auth::user()->name }}</div>
                                    <div class="text-[11px] text-slate-500 truncate mt-0.5">{{ Auth::user()->email }}</div>
                                    <div class="mt-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ Auth::user()->isSuperAdmin() ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200' }}">
                                            {{ Auth::user()->isSuperAdmin() ? 'Super Admin' : 'Program Staff' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Items -->
                                <div class="py-1 space-y-0.5">
                                    <a 
                                        href="{{ route('profile.edit') }}" 
                                        class="flex items-center gap-2.5 px-3 py-2 text-slate-700 hover:text-slate-900 hover:bg-slate-50 rounded-xl transition font-medium"
                                    >
                                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                        </svg>
                                        <span>Profile Settings</span>
                                    </a>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition font-medium text-left"
                                        >
                                            <svg class="w-4 h-4 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                                <polyline points="16 17 21 12 16 7"></polyline>
                                                <line x1="21" y1="12" x2="9" y2="12"></line>
                                            </svg>
                                            <span>Log Out</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Main Content Area: Expanded Full-Width Canvas -->
                <main class="flex-1 px-4 sm:px-8 lg:px-10 py-6 sm:py-8 w-full">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
