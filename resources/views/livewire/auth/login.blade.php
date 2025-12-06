<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="text-center">
            <h1 class="text-xl font-medium text-[var(--text-primary)] mb-2">Log in to your account</h1>
            <p class="text-sm text-[var(--text-secondary)]">Enter your email and password below to log in</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="p-3 bg-[var(--accent-green-light)] border border-[rgba(76,175,80,0.3)] rounded-lg text-center">
                <p class="text-sm text-[var(--accent-green)]">{{ session('status') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <div class="space-y-2">
                <label for="email" class="block text-sm text-[var(--text-primary)]">Email address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
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
                        <a href="{{ route('password.request') }}" wire:navigate class="text-xs text-[var(--accent-pink)] hover:underline">
                            Forgot your password?
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
                    type="checkbox" 
                    name="remember" 
                    {{ old('remember') ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-[var(--border)] bg-[var(--bg-tertiary)] text-[#E94B8C] focus:ring-[#E94B8C] focus:ring-offset-0"
                />
                <span class="text-sm text-[var(--text-secondary)]">Remember me</span>
            </label>

            <button
                type="submit"
                class="w-full px-6 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors text-sm font-medium"
                data-test="login-button"
            >
                Log in
            </button>
        </form>

        @if (Route::has('register'))
            <div class="text-sm text-center text-[var(--text-secondary)]">
                <span>Don't have an account?</span>
                <a href="{{ route('register') }}" wire:navigate class="text-[var(--accent-pink)] hover:underline ml-1">
                    Sign up
                </a>
            </div>
        @endif
    </div>
</x-layouts.auth>
