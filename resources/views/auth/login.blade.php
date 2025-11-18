<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Axia') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <!-- Logo and Title -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Axia</h1>
                <p class="text-gray-600">An AI Focus Coach to align your to-dos with what actually moves your KPIs.</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 text-sm font-medium text-green-600 text-center">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email address
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="email@example.com"
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                        />
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- First Name (Optional) -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                            First name <span class="text-gray-400">(optional)</span>
                        </label>
                        <input
                            id="first_name"
                            name="first_name"
                            type="text"
                            autocomplete="given-name"
                            placeholder="John"
                            value="{{ old('first_name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                        />
                    </div>

                    <!-- Last Name (Optional) -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Last name <span class="text-gray-400">(optional)</span>
                        </label>
                        <input
                            id="last_name"
                            name="last_name"
                            type="text"
                            autocomplete="family-name"
                            placeholder="Doe"
                            value="{{ old('last_name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                        />
                    </div>

                    <!-- Continue Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 px-4 rounded-xl hover:from-rose-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all shadow-sm"
                        >
                            Continue
                        </button>
                    </div>
                </form>

                <!-- Guest Login -->
                <div class="mt-6 text-center">
                    <form method="POST" action="{{ route('register.store') }}">
                        @csrf
                        <input type="hidden" name="is_guest" value="1">
                        <button
                            type="submit"
                            class="text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors"
                        >
                            Continue as guest
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

