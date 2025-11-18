<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Axia') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 antialiased">
    
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-rose-500 to-pink-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">A</span>
                        </div>
                        <span class="text-xl font-bold text-gray-900">Axia</span>
                    </a>
                </div>

                <!-- User Menu -->
                <div class="flex items-center gap-4">
                    @auth
                        <div x-data="{ open: false }" class="relative">
                <button 
                    @click="open = !open"
                    @click.away="open = false"
                    class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <div class="w-9 h-9 bg-gradient-to-br from-rose-500 to-pink-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-sm font-semibold">
                            {{ auth()->user()->is_guest ? 'G' : strtoupper(substr(auth()->user()->email ?? 'U', 0, 1)) }}
                        </span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                            <!-- Dropdown -->
                            <div 
                                x-show="open"
                                x-transition
                                class="absolute right-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden"
                                style="display: none; width: 240px;"
                            >
                                @if(!auth()->user()->is_guest)
                                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ auth()->user()->full_name ?: 'User' }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ auth()->user()->email }}</p>
                                    </div>
                                @else
                                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                                        <p class="text-sm font-semibold text-gray-900">Guest User</p>
                                        <p class="text-xs text-gray-500 mt-0.5">No account yet</p>
                                    </div>
                                @endif

                                <div class="py-2">
                                    <a href="{{ route('company.edit') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg style="width: 16px; height: 16px; flex-shrink: 0;" class="text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                                        </svg>
                                        <span>Company Settings</span>
                                    </a>

                                    <a href="{{ route('goals.edit') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg style="width: 16px; height: 16px; flex-shrink: 0;" class="text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                        </svg>
                                        <span>Goals & KPIs</span>
                                    </a>
                                </div>

                                <div class="border-t border-gray-100 py-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 text-left transition-colors">
                                            <svg style="width: 16px; height: 16px; flex-shrink: 0;" class="text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                            </svg>
                                            <span>Sign out</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
