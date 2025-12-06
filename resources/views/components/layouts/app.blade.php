<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ 
    isDark: localStorage.getItem('axia-theme') !== 'light',
    init() {
        this.$watch('isDark', val => {
            localStorage.setItem('axia-theme', val ? 'dark' : 'light');
            if (val) {
                document.documentElement.classList.remove('light');
            } else {
                document.documentElement.classList.add('light');
            }
        });
        // Apply initial theme
        if (!this.isDark) {
            document.documentElement.classList.add('light');
        }
    }
}" :class="{ 'light': !isDark }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Axia') }}</title>
    
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Axia User Context for JavaScript -->
    <script>
        window.axiaUser = {
            id: '{{ auth()->id() }}',
            name: '{{ auth()->user()?->name }}',
            email: '{{ auth()->user()?->email }}',
            company_id: '{{ auth()->user()?->company_id }}'
        };
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="h-screen overflow-hidden bg-[var(--bg-primary)] text-[var(--text-primary)]" x-data="{ chatOpen: false }">
    <div class="flex h-screen">
        
        <!-- Mobile Chat Toggle Button -->
        <button
            @click="chatOpen = !chatOpen"
            class="lg:hidden fixed bottom-4 left-4 z-50 w-12 h-12 bg-[#E94B8C] text-white rounded-full shadow-lg flex items-center justify-center"
        >
            <svg x-show="!chatOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <svg x-show="chatOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        
        <!-- Chat Panel (Left Sidebar) -->
        <aside 
            class="w-[270px] h-screen bg-[var(--bg-secondary)] border-r border-[var(--border)] flex flex-col shrink-0 fixed lg:relative z-40 transition-transform duration-300"
            :class="{ '-translate-x-full lg:translate-x-0': !chatOpen, 'translate-x-0': chatOpen }"
        >
            <!-- Logo -->
            <div class="p-6 border-b border-[var(--border)]">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#E94B8C] to-[#B03A6F] flex items-center justify-center">
                        <span class="text-white text-sm font-medium">A</span>
                    </div>
                    <span class="text-[var(--text-primary)] font-medium">Axia</span>
                </a>
            </div>

            <!-- Chat Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar" id="chat-messages">
                @php
                    $hasCompany = auth()->user()?->company?->name;
                    $hasGoals = auth()->user()?->company?->goals?->count() > 0;
                @endphp
                
                @if($hasCompany && $hasGoals)
                    <!-- Example conversation for users with data -->
                    <div class="p-3 rounded-lg bg-[var(--bg-primary)] mr-4">
                        <p class="text-sm text-[var(--text-primary)]">Hey! I see you're running <span class="font-medium">{{ auth()->user()->company->name }}</span>. How can I help you prioritize today?</p>
                    </div>
                    
                    <div class="p-3 rounded-lg bg-[var(--bg-tertiary)] ml-4">
                        <p class="text-sm text-[var(--text-primary)]">I have a lot on my plate. Can you help me figure out what to focus on?</p>
                    </div>
                    
                    <div class="p-3 rounded-lg bg-[var(--bg-primary)] mr-4 space-y-2">
                        <p class="text-sm text-[var(--text-primary)]">Of course! Based on your goal to <span class="text-[var(--accent-pink)]">"{{ auth()->user()->company->goals->first()?->title ?? 'reach your targets' }}"</span>, I recommend focusing on tasks that directly drive customer acquisition.</p>
                        <p class="text-sm text-[var(--text-secondary)]">Paste your to-do list in the To-Dos tab and I'll analyze each task's impact on your goals.</p>
                    </div>
                    
                    <div class="p-3 rounded-lg bg-[var(--bg-tertiary)] ml-4">
                        <p class="text-sm text-[var(--text-primary)]">Great, I just added my tasks. What should I do first?</p>
                    </div>
                    
                    <div class="p-3 rounded-lg bg-[var(--bg-primary)] mr-4">
                        <p class="text-sm text-[var(--text-primary)]">Running analysis now... Check the <span class="text-[var(--accent-pink)]">Analysis</span> tab for your prioritized results with focus scores!</p>
                    </div>
                @else
                    <!-- Onboarding message for new users -->
                    <div class="p-3 rounded-lg bg-[var(--bg-primary)] mr-4 space-y-3">
                        <p class="text-sm text-[var(--text-primary)] font-medium">Hey! I'm Axia, your AI focus sparring partner.</p>
                        <p class="text-sm text-[var(--text-secondary)]">I help founders prioritize their tasks by analyzing how each to-do impacts their business goals.</p>
                        <div class="text-sm text-[var(--text-secondary)]">
                            <p class="mb-1">To get started:</p>
                            <p class="pl-2">1. Tell me about your <a href="{{ route('company.edit') }}" wire:navigate class="text-[var(--accent-pink)] hover:underline">company</a></p>
                            <p class="pl-2">2. Share your <a href="{{ route('goals.edit') }}" wire:navigate class="text-[var(--accent-pink)] hover:underline">goals</a></p>
                            <p class="pl-2">3. Paste your to-do list</p>
                            <p class="mt-2">Then I'll show you what to focus on first.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Chat Input -->
            <div class="p-4 border-t border-[var(--border)]">
                <form id="chat-form" class="relative">
                    <input
                        type="text"
                        name="message"
                        placeholder="Ask Axia..."
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-2.5 pr-10 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                    />
                    <button
                        type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Step Bar / Header -->
            <header class="h-14 bg-gradient-to-b from-[var(--bg-secondary)] to-[var(--bg-primary)] border-b border-[var(--border)] flex items-center justify-between px-4 lg:px-6 shrink-0">
                <!-- Navigation Pills -->
                @php
                    $currentRoute = request()->route()->getName();
                    $steps = [
                        ['id' => 'company', 'label' => 'Company', 'route' => 'company.edit'],
                        ['id' => 'goals', 'label' => 'Goals', 'route' => 'goals.edit'],
                        ['id' => 'todos', 'label' => 'To-Dos', 'route' => 'home'],
                        ['id' => 'analysis', 'label' => 'Analysis', 'route' => 'results.show'],
                    ];
                    $currentIndex = 0;
                    foreach ($steps as $index => $step) {
                        if ($step['route'] === $currentRoute || str_starts_with($currentRoute, explode('.', $step['route'])[0])) {
                            $currentIndex = $index;
                            break;
                        }
                    }
                @endphp
                
                <div class="relative flex items-center gap-0.5 md:gap-1 bg-[var(--bg-tertiary)] rounded-full p-1 overflow-x-auto">
                    <!-- Sliding Highlight -->
                    <div 
                        class="absolute h-[calc(100%-8px)] bg-[#E94B8C] rounded-full transition-all duration-300 ease-out hidden md:block"
                        style="width: calc(25% - 4px); left: calc({{ $currentIndex * 25 }}% + 4px);"
                    ></div>
                    
                    @foreach($steps as $index => $step)
                        @php
                            $isActive = $index === $currentIndex;
                            $routeExists = $step['route'] !== 'results.show' || (isset($lastRun) && $lastRun);
                        @endphp
                        <a 
                            @if($routeExists && $step['route'] !== 'results.show')
                                href="{{ route($step['route']) }}"
                                wire:navigate
                            @elseif($step['route'] === 'results.show' && isset($lastRun) && $lastRun)
                                href="{{ route('results.show', $lastRun) }}"
                                wire:navigate
                            @else
                                href="#"
                            @endif
                            class="relative z-10 px-2 md:px-4 py-1.5 rounded-full flex items-center gap-1 md:gap-2 transition-colors whitespace-nowrap {{ $isActive ? 'text-white bg-[#E94B8C] md:bg-transparent' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)]' }}"
                        >
                            @switch($step['id'])
                                @case('company')
                                    <svg class="w-3.5 h-3.5 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    @break
                                @case('goals')
                                    <svg class="w-3.5 h-3.5 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <circle cx="12" cy="12" r="6" />
                                        <circle cx="12" cy="12" r="2" />
                                    </svg>
                                    @break
                                @case('todos')
                                    <svg class="w-3.5 h-3.5 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    @break
                                @case('analysis')
                                    <svg class="w-3.5 h-3.5 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    @break
                            @endswitch
                            <span class="text-xs md:text-sm">{{ $step['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <!-- Right Side: Theme Toggle & Profile -->
                <div class="flex items-center gap-4">
                    <!-- Theme Toggle -->
                    <button
                        @click="isDark = !isDark"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)] transition-all"
                    >
                        <template x-if="isDark">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <circle cx="12" cy="12" r="5" />
                                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                            </svg>
                        </template>
                        <template x-if="!isDark">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                            </svg>
                        </template>
                    </button>
                    
                    <!-- Profile Dropdown -->
                    @auth
                        <x-ui.dropdown align="right" width="240px">
                            <x-slot name="trigger">
                                <button class="w-8 h-8 rounded-full bg-gradient-to-br from-[#E94B8C] to-[#B03A6F] flex items-center justify-center hover:opacity-90 transition-opacity">
                                    <span class="text-white text-sm font-medium">
                                        {{ auth()->user()->is_guest ? 'G' : strtoupper(substr(auth()->user()->email ?? 'U', 0, 1)) }}
                                    </span>
                                </button>
                            </x-slot>
                            
                            <!-- Header -->
                            <x-ui.dropdown-header 
                                :title="auth()->user()->is_guest ? 'Guest User' : (auth()->user()->full_name ?: 'User')"
                                :subtitle="auth()->user()->is_guest ? 'No account yet' : auth()->user()->email"
                            />
                            
                            <!-- Menu Items -->
                            <div class="py-2">
                                <x-ui.dropdown-item href="{{ route('profile.edit') }}" wire:navigate>
                                    <x-slot name="icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                    </x-slot>
                                    Profile Settings
                                </x-ui.dropdown-item>
                                
                                <x-ui.dropdown-item href="{{ route('analyses.index') }}" wire:navigate>
                                    <x-slot name="icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                                        </svg>
                                    </x-slot>
                                    All Analyses
                                </x-ui.dropdown-item>
                            </div>
                            
                            <x-ui.dropdown-divider />
                            
                            <!-- Logout -->
                            <div class="py-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-ui.dropdown-item type="submit">
                                        <x-slot name="icon">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                            </svg>
                                        </x-slot>
                                        Sign out
                                    </x-ui.dropdown-item>
                                </form>
                            </div>
                        </x-ui.dropdown>
                    @endauth
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto custom-scrollbar">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts

    <!-- FullCalendar CDN (if needed) -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js'></script>

    <!-- Chart.js CDN (if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @stack('scripts')
</body>

</html>
