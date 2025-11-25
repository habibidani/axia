<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'axia') }} - Sign In</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-50 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <!-- Logo and Title -->
            <div class="text-center mb-8">
                <img src="{{ asset('images/axia-logo.svg') }}" alt="axia" class="h-12 mx-auto mb-4">
                <p class="text-gray-600">Welcome back!</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 text-sm font-medium text-green-600 text-center">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Sign in to your account</h2>

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email address
                        </label>
                        <input id="email" name="email" type="email" required autocomplete="email" autofocus
                            placeholder="email@example.com" value="{{ old('email') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all" />
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password
                        </label>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            placeholder="Enter your password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all" />
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="w-4 h-4 text-rose-500 border-gray-300 rounded focus:ring-rose-500" />
                        <label for="remember" class="ml-3 text-sm text-gray-600">
                            Remember me
                        </label>
                    </div>

                    <!-- Sign In Button -->
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 px-4 rounded-xl hover:from-rose-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all shadow-sm">
                            Sign in
                        </button>
                    </div>
                </form>

                <!-- Don't have account -->
                <div class="mt-6 text-center">
                    <span class="text-sm text-gray-600">Don't have an account? </span>
                    <a href="{{ route('register') }}" class="text-sm font-medium text-rose-600 hover:text-rose-700">
                        Sign up
                    </a>
                </div>
            </div>

            <!-- Guest Login Box -->
            <div class="mt-4 bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-center">
                <form method="POST" action="{{ route('register.store') }}">
                    @csrf
                    <input type="hidden" name="is_guest" value="1">
                    <button type="submit"
                        class="w-full px-6 py-3 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-xl hover:bg-gray-100 transition-colors">
                        Continue as guest
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
