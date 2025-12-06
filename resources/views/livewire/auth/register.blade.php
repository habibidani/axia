<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="text-center">
            <h1 class="text-xl font-medium text-[var(--text-primary)] mb-2">Create an account</h1>
            <p class="text-sm text-[var(--text-secondary)]">Enter your details below to create your account</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="p-3 bg-[var(--accent-green-light)] border border-[rgba(76,175,80,0.3)] rounded-lg text-center">
                <p class="text-sm text-[var(--accent-green)]">{{ session('status') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Name -->
            <div class="space-y-2">
                <label for="name" class="block text-sm text-[var(--text-primary)]">Full name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="John Doe"
                    value="{{ old('name') }}"
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                />
                @error('name')
                    <p class="text-xs text-[var(--accent-orange)]">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="space-y-2">
                <label for="email" class="block text-sm text-[var(--text-primary)]">Email address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
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
                <label for="password" class="block text-sm text-[var(--text-primary)]">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Password"
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                />
                @error('password')
                    <p class="text-xs text-[var(--accent-orange)]">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="space-y-2">
                <label for="password_confirmation" class="block text-sm text-[var(--text-primary)]">Confirm password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm password"
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                />
            </div>

            <button
                type="submit"
                class="w-full px-6 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors text-sm font-medium"
                data-test="register-user-button"
            >
                Create account
            </button>
        </form>

        <div class="text-sm text-center text-[var(--text-secondary)]">
            <span>Already have an account?</span>
            <a href="{{ route('login') }}" wire:navigate class="text-[var(--accent-pink)] hover:underline ml-1">
                Log in
            </a>
        </div>
    </div>
</x-layouts.auth>
