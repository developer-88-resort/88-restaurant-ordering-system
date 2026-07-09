<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @if (file_exists(public_path('images/logo.png')))
            <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-[#F7F0E3]">

            {{-- Top bar --}}
            <header class="bg-white border-b border-[#E5DDD0] sticky top-0 z-30">
                <div class="px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <button @click="sidebarOpen = true" type="button" class="lg:hidden -ml-2 p-2 rounded-md text-gray-500 hover:bg-gray-100" aria-label="{{ __('Open menu') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                            </svg>
                        </button>

                        <a href="{{ route('superadmin.dashboard') }}" class="shrink-0">
                            @if (file_exists(public_path('images/logo2024.png')))
                                <img src="{{ asset('images/logo2024.png') }}" alt="88 Hot Spring Resort" class="h-9 w-auto object-contain">
                            @else
                                <img src="{{ asset('images/logo.png') }}" alt="88 Hot Spring Resort" class="h-10 w-10 rounded-full object-cover">
                            @endif
                        </a>
                        <span class="hidden sm:inline text-[#D9CCBA]">|</span>
                        <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-[#F3E1DC] text-[#8A3330]">
                            {{ Auth::user()->role->label() }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-800 {{ request()->routeIs('profile.edit') ? 'font-semibold text-[#8A3330]' : '' }}">
                            <x-avatar :user="Auth::user()" class="h-9 w-9 text-xs" />
                            <span class="hidden sm:inline">
                                {{ __('Signed in as') }} <span class="font-semibold text-gray-800">{{ Auth::user()->name }}</span>
                            </span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="px-3 sm:px-4 py-1.5 rounded-lg border border-[#D9CCBA] text-sm font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('Sign out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <x-toast />

            <div class="flex">

                {{-- Mobile backdrop --}}
                <div x-show="sidebarOpen" x-cloak x-transition.opacity
                     @click="sidebarOpen = false"
                     class="fixed inset-0 bg-black/40 z-40 lg:hidden"></div>

                {{-- Sidebar --}}
                <aside
                    x-cloak
                    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                    class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-[#E5DDD0] px-4 py-6 overflow-y-auto transform transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 lg:z-auto lg:w-60 lg:shrink-0 lg:min-h-[calc(100vh-4rem)]"
                >
                    <div class="flex items-center justify-between mb-3 lg:hidden">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 px-2">{{ __('Menu') }}</p>
                        <button @click="sidebarOpen = false" type="button" class="p-2 rounded-md text-gray-500 hover:bg-gray-100" aria-label="{{ __('Close menu') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <p class="hidden lg:block text-xs font-semibold uppercase tracking-wider text-gray-400 px-2 mb-3">{{ __('Menu') }}</p>

                    <nav class="space-y-1" @click="sidebarOpen = false">
                        <x-sidebar-link :href="route('superadmin.dashboard')" :active="request()->routeIs('superadmin.dashboard')">
                            {{ __('Overview') }}
                        </x-sidebar-link>

                        <x-sidebar-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                            {{ __('Order Management') }}
                        </x-sidebar-link>

                        <x-sidebar-link :href="route('menu-items.index')" :active="request()->routeIs('menu-items.*') || request()->routeIs('menu-categories.*')">
                            {{ __('Menu Management') }}
                        </x-sidebar-link>

                        <x-sidebar-link :href="route('spaces.index')" :active="request()->routeIs('spaces.*') || request()->routeIs('areas.*') || request()->routeIs('space-categories.*')">
                            {{ __('Spaces') }}
                        </x-sidebar-link>
                        <x-sidebar-link :href="route('kitchen.index')" :active="request()->routeIs('kitchen.*')">
                            {{ __('Kitchen') }}
                        </x-sidebar-link>
                        <x-sidebar-link :href="route('superadmin.reports.index')" :active="request()->routeIs('superadmin.reports.*')">
                            {{ __('Reports') }}
                        </x-sidebar-link>

                        <x-sidebar-link :href="route('superadmin.users.index')" :active="request()->routeIs('superadmin.users.*')">
                            {{ __('User Management') }}
                        </x-sidebar-link>

                        <x-sidebar-link :href="route('superadmin.audit-logs.index')" :active="request()->routeIs('superadmin.audit-logs.*')">
                            {{ __('Audit Logs') }}
                        </x-sidebar-link>
                        <x-sidebar-link :href="route('superadmin.settings.edit')" :active="request()->routeIs('superadmin.settings.*')">
                            {{ __('Settings') }}
                        </x-sidebar-link>
                    </nav>
                </aside>

                {{-- Page Content --}}
                <main class="flex-1 min-w-0 p-4 sm:p-6">
                    @isset($header)
                        <div class="mb-6">
                            {{ $header }}
                        </div>
                    @endisset

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
