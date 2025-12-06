<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->first_name = Auth::user()->first_name ?? '';
        $this->last_name = Auth::user()->last_name ?? '';
        $this->email = Auth::user()->email ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated');
        session()->flash('success', 'Profile updated successfully.');
    }
}; ?>

<div class="max-w-2xl mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-[var(--text-primary)] mb-2">Profile Settings</h1>
        <p class="text-[var(--text-secondary)]">Update your personal information.</p>
    </div>

    <!-- Success Message -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-[var(--accent-green-light)] border border-[rgba(76,175,80,0.3)] rounded-xl">
            <p class="text-sm text-[var(--accent-green)]">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Profile Form -->
    <div class="bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] p-8">
        <form wire:submit="updateProfileInformation" class="space-y-6">
            
            <!-- Avatar Preview -->
            <div class="flex items-center gap-4 pb-6 border-b border-[var(--border)]">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#E94B8C] to-[#B03A6F] flex items-center justify-center">
                    <span class="text-white text-2xl font-medium">
                        {{ strtoupper(substr($email, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <p class="text-[var(--text-primary)] font-medium">{{ $first_name }} {{ $last_name }}</p>
                    <p class="text-sm text-[var(--text-secondary)]">{{ $email }}</p>
                </div>
            </div>

            <!-- Name Fields -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-[var(--text-primary)] mb-2">First name</label>
                    <input
                        type="text"
                        wire:model="first_name"
                        placeholder="John"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                    />
                    @error('first_name') <span class="text-xs text-[var(--accent-orange)] mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-[var(--text-primary)] mb-2">Last name</label>
                    <input
                        type="text"
                        wire:model="last_name"
                        placeholder="Doe"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                    />
                    @error('last_name') <span class="text-xs text-[var(--accent-orange)] mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm text-[var(--text-primary)] mb-2">Email address</label>
                <input
                    type="email"
                    wire:model="email"
                    placeholder="email@example.com"
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors"
                />
                @error('email') <span class="text-xs text-[var(--accent-orange)] mt-1">{{ $message }}</span> @enderror
            </div>

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                <div class="p-4 bg-[rgba(255,183,77,0.1)] border border-[rgba(255,183,77,0.3)] rounded-xl">
                    <p class="text-sm text-[#FFB74D]">
                        Your email address is unverified.
                        <button type="button" wire:click="resendVerificationNotification" class="underline hover:no-underline">
                            Click here to re-send the verification email.
                        </button>
                    </p>
                </div>
            @endif

            <!-- Submit -->
            <div class="flex justify-end pt-4">
                <button
                    type="submit"
                    class="px-8 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    @if(!auth()->user()->is_guest)
        <div class="mt-8 bg-[var(--bg-secondary)] rounded-2xl border border-[rgba(255,138,101,0.3)] p-8">
            <h3 class="text-lg font-medium text-[var(--accent-orange)] mb-2">Danger Zone</h3>
            <p class="text-sm text-[var(--text-secondary)] mb-4">
                Once you delete your account, there is no going back. Please be certain.
            </p>
            <button
                type="button"
                onclick="confirm('Are you sure you want to delete your account? This action cannot be undone.') || event.stopImmediatePropagation()"
                wire:click="$dispatch('delete-account')"
                class="px-6 py-2 bg-[rgba(255,138,101,0.1)] hover:bg-[rgba(255,138,101,0.2)] text-[var(--accent-orange)] border border-[rgba(255,138,101,0.3)] rounded-lg transition-colors text-sm"
            >
                Delete Account
            </button>
        </div>
    @endif
</div>
