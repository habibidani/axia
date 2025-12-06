<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ isDark: true }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Axia') }}</title>
    
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
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)] antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-sm">
            <!-- Logo -->
            <a href="{{ route('welcome') }}" class="flex flex-col items-center gap-3 mb-8" wire:navigate>
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#E94B8C] to-[#B03A6F] flex items-center justify-center">
                    <span class="text-white text-xl font-medium">A</span>
                </div>
                <span class="text-lg font-medium text-[var(--text-primary)]">Axia</span>
            </a>
            
            <!-- Content Card -->
            <div class="bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
    
    @livewireScripts
    @if(config('app.debug'))
        <script src="/flux/flux.js" data-navigate-once></script>
    @else
        <script src="/flux/flux.min.js" data-navigate-once></script>
    @endif
</body>
</html>
