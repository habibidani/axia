<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Axia') }} - Sign In</title>
    
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--bg-primary)] text-[var(--text-primary)] antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <!-- Logo and Title -->
            <a href="{{ route('welcome') }}" class="flex flex-col items-center gap-3 mb-8">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#E94B8C] to-[#B03A6F] flex items-center justify-center">
                    <span class="text-white text-xl font-medium">A</span>
                </div>
                <span class="text-lg font-medium text-[var(--text-primary)]">Axia</span>
            </a>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-6 p-4 bg-[var(--accent-green-light)] border border-[rgba(76,175,80,0.3)] rounded-xl text-center">
                    <p class="text-sm text-[var(--accent-green)]">{{ session('status') }}</p>
                </div>
            @endif

            <!-- Login Card -->
            <div class="bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] p-8">
                <h2 class="text-xl font-medium text-[var(--text-primary)] mb-2 text-center">Log in to your account</h2>
                <p class="text-sm text-[var(--text-secondary)] mb-6 text-center">Enter your email and password below</p>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

                    <!-- Email Address -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm text-[var(--text-primary)]">Email address</label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            autocomplete="email" 
                            autofocus
                            placeholder="email@example.com" 
                            value="{{ old('email') }}"
                            class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                        />
                        @error('email')
                            <p class="text-xs text-[var(--accent-orange)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm text-[var(--text-primary)]">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs text-[var(--accent-pink)] hover:underline">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            autocomplete="current-password"
                            placeholder="Password"
                            class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                        />
                        @error('password')
                            <p class="text-xs text-[var(--accent-orange)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            id="remember" 
                            name="remember" 
                            type="checkbox"
                            class="w-4 h-4 rounded border-[var(--border)] bg-[var(--bg-tertiary)] text-[#E94B8C] focus:ring-[#E94B8C] focus:ring-offset-0"
                        />
                        <span class="text-sm text-[var(--text-secondary)]">Remember me</span>
                    </label>

                    <!-- Sign In Button -->
                    <button 
                        type="submit"
                        class="w-full px-6 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors text-sm font-medium"
                    >
                        Log in
                    </button>
                </form>

                <!-- Don't have account -->
                <div class="mt-6 text-center">
                    <span class="text-sm text-[var(--text-secondary)]">Don't have an account? </span>
                    <a href="{{ route('register') }}" class="text-sm text-[var(--accent-pink)] hover:underline">
                        Sign up
                    </a>
                </div>
            </div>

            <!-- Guest Login Box -->
            <div class="mt-4 bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] p-6 text-center">
                <form method="POST" action="{{ route('register.store') }}">
                    @csrf
                    <input type="hidden" name="is_guest" value="1">
                    <button 
                        type="submit"
                        class="w-full px-6 py-3 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] text-[var(--text-primary)] border border-[var(--border)] rounded-lg transition-colors text-sm"
                    >
                        Continue as guest
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
