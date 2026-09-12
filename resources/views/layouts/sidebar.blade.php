<!-- Desktop & Mobile Sidebar Navigation -->
<aside 
    :class="[
        sidebarCollapsed ? 'lg:w-20' : 'lg:w-64',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]"
    class="fixed inset-y-0 left-0 z-50 w-64 h-full bg-white border-r border-slate-200/80 flex flex-col justify-between transition-all duration-200 ease-in-out lg:static lg:h-screen lg:shrink-0 select-none overflow-y-auto"
>
    <!-- Brand & Top Navigation -->
    <div class="p-4 flex flex-col h-full">
        <!-- Logo Header -->
        <div class="flex items-center justify-between pb-6" :class="sidebarCollapsed ? 'lg:justify-center' : ''">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group" :title="sidebarCollapsed ? '{{ $platformName }}' : ''">
                <div class="w-9 h-9 rounded-xl overflow-hidden shadow-xs ring-1 ring-slate-900/10 group-hover:scale-105 transition-transform duration-200 shrink-0 bg-black">
                    <img src="{{ $platformLogo }}" alt="{{ $platformName }} Logo" class="w-full h-full object-cover">
                </div>
                <div 
                    x-show="!sidebarCollapsed" 
                    x-transition.opacity 
                    class="flex flex-col"
                >
                    <span class="font-bold text-slate-900 tracking-tight text-base whitespace-nowrap leading-tight">
                        {{ $platformName }}
                    </span>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                        {{ $platformTagline }}
                    </span>
                </div>
            </a>

            <!-- Sidebar collapse / toggle icon matching reference -->
            <button 
                type="button" 
                @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebar_collapsed', sidebarCollapsed)"
                class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition hidden lg:block"
                :class="sidebarCollapsed ? 'lg:hidden' : ''"
                title="Collapse sidebar"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="9" y1="3" x2="9" y2="21"></line>
                </svg>
            </button>
        </div>

        <!-- Mini Expand Button when sidebar is collapsed on desktop -->
        <div x-show="sidebarCollapsed" class="hidden lg:flex justify-center pb-4">
            <button 
                type="button" 
                @click="sidebarCollapsed = false; localStorage.setItem('sidebar_collapsed', false)"
                class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition"
                title="Expand sidebar"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="9" y1="3" x2="9" y2="21"></line>
                </svg>
            </button>
        </div>

        <!-- Section Label: Main Menu -->
        <div 
            x-show="!sidebarCollapsed" 
            x-transition.opacity
            class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 px-3 mt-1 mb-2 whitespace-nowrap"
        >
            Main Menu
        </div>
        <div x-show="sidebarCollapsed" class="w-8 h-[1px] bg-slate-200 mx-auto my-2 hidden lg:block"></div>

        <!-- Navigation Links -->
        <nav class="space-y-1.5 flex-1">
            <!-- Dashboard -->
            <a 
                href="{{ route('dashboard') }}" 
                class="flex items-center gap-3 rounded-xl text-sm transition duration-150 {{ request()->routeIs('dashboard') ? 'bg-slate-100/90 text-slate-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}"
                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2.5 py-2.5 px-3.5' : 'px-3.5 py-2.5'"
                :title="sidebarCollapsed ? 'Dashboard' : ''"
            >
                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-slate-900' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18"/>
                    <path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Dashboard</span>
            </a>

            <!-- Sponsors -->
            <a 
                href="{{ route('admin.sponsors.index') }}" 
                class="flex items-center gap-3 rounded-xl text-sm transition duration-150 {{ request()->routeIs('admin.sponsors.*') ? 'bg-slate-100/90 text-slate-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}"
                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2.5 py-2.5 px-3.5' : 'px-3.5 py-2.5'"
                :title="sidebarCollapsed ? 'Sponsors' : ''"
            >
                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.sponsors.*') ? 'text-slate-900' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Sponsors</span>
            </a>

            <!-- Reminder Waves (Super Admin only) -->
            @if(Auth::user()->isSuperAdmin())
                <a 
                    href="{{ route('admin.settings.index') }}" 
                    class="flex items-center gap-3 rounded-xl text-sm transition duration-150 {{ request()->routeIs('admin.settings.index') ? 'bg-slate-100/90 text-slate-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-2.5 py-2.5 px-3.5' : 'px-3.5 py-2.5'"
                    :title="sidebarCollapsed ? 'Reminder Waves' : ''"
                >
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.settings.index') ? 'text-slate-900' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                        <line x1="2" y1="10" x2="22" y2="10"></line>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Reminder Waves</span>
                </a>

                <!-- Custom Reminders (Super Admin only) -->
                <a 
                    href="{{ route('admin.custom-reminders.index') }}" 
                    class="flex items-center gap-3 rounded-xl text-sm transition duration-150 {{ request()->routeIs('admin.custom-reminders.*') ? 'bg-slate-100/90 text-slate-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-2.5 py-2.5 px-3.5' : 'px-3.5 py-2.5'"
                    :title="sidebarCollapsed ? 'Custom Reminders' : ''"
                >
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.custom-reminders.*') ? 'text-slate-900' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Custom Reminders</span>
                </a>

                <!-- Platform Branding (Super Admin only) -->
                <a 
                    href="{{ route('admin.settings.platform') }}" 
                    class="flex items-center gap-3 rounded-xl text-sm transition duration-150 {{ request()->routeIs('admin.settings.platform') ? 'bg-slate-100/90 text-slate-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-2.5 py-2.5 px-3.5' : 'px-3.5 py-2.5'"
                    :title="sidebarCollapsed ? 'Platform Branding' : ''"
                >
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.settings.platform') ? 'text-slate-900' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Platform Branding</span>
                </a>
            @endif

            <!-- Delivery Logs -->
            <a 
                href="{{ route('admin.logs.index') }}" 
                class="flex items-center gap-3 rounded-xl text-sm transition duration-150 {{ request()->routeIs('admin.logs.*') ? 'bg-slate-100/90 text-slate-900 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}"
                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2.5 py-2.5 px-3.5' : 'px-3.5 py-2.5'"
                :title="sidebarCollapsed ? 'Delivery Logs' : ''"
            >
                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.logs.*') ? 'text-slate-900' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Delivery Logs</span>
            </a>
        </nav>

        <!-- Bottom Menu Links -->
        <div class="pt-4 border-t border-slate-200/80 space-y-1.5">
            <!-- Settings / Profile -->
            <a 
                href="{{ route('profile.edit') }}" 
                class="flex items-center gap-3 rounded-xl text-sm transition duration-150 {{ request()->routeIs('profile.edit') ? 'bg-slate-100/90 text-slate-900 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium' }}"
                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2.5 py-2.5 px-3.5' : 'px-3.5 py-2.5'"
                :title="sidebarCollapsed ? 'Settings' : ''"
            >
                <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Settings</span>
            </a>

            <!-- Help & Center -->
            <a 
                href="https://github.com/SejatiDimedia/rymainder#readme" 
                target="_blank"
                class="flex items-center gap-3 rounded-xl text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium transition duration-150"
                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2.5 py-2.5 px-3.5' : 'px-3.5 py-2.5'"
                :title="sidebarCollapsed ? 'Help & Center' : ''"
            >
                <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Help & Center</span>
            </a>

            <!-- Log Out -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button 
                    type="submit" 
                    class="w-full flex items-center gap-3 rounded-xl text-sm text-rose-600 hover:text-rose-700 hover:bg-rose-50 font-medium transition duration-150"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-2.5 py-2.5 px-3.5' : 'px-3.5 py-2.5'"
                    :title="sidebarCollapsed ? 'Log Out' : ''"
                >
                    <svg class="w-4 h-4 text-rose-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity class="whitespace-nowrap">Log Out</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Backdrop for mobile drawer -->
<div 
    x-show="sidebarOpen" 
    @click="sidebarOpen = false" 
    x-cloak 
    class="fixed inset-0 z-40 bg-slate-900/30 backdrop-blur-xs lg:hidden transition-opacity"
></div>
