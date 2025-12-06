<div class="max-w-3xl mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-[var(--text-primary)] mb-2">Company Information</h1>
        <p>Give Axia your business context.</p>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-[var(--accent-green-light)] border border-[rgba(76,175,80,0.3)] rounded-xl">
            <p class="text-sm text-[var(--accent-green)]">{{ session('success') }}</p>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-[rgba(255,138,101,0.1)] border border-[rgba(255,138,101,0.3)] rounded-xl">
            <p class="text-sm text-[var(--accent-orange)]">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)]">
        <form wire:submit.prevent="save" class="space-y-6">

            <!-- Website / Domain -->
            <div class="space-y-4">
                <label class="block text-sm text-[var(--text-primary)]">Website</label>
                <div class="flex gap-3">
                    <div class="flex-1 relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" />
                        </svg>
                        <input
                            type="url"
                            wire:model="website"
                            placeholder="https://example.com"
                            class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg pl-10 pr-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)]"
                        />
                    </div>
                </div>
                @error('website') <span class="text-xs text-[var(--accent-orange)]">{{ $message }}</span> @enderror
            </div>

            <div class="h-px bg-[var(--border)]"></div>

            <!-- Company Name -->
            <div>
                <label class="block text-sm text-[var(--text-primary)] mb-2">Company Name</label>
                <input
                    type="text"
                    wire:model="name"
                    placeholder="Enter company name"
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)]"
                />
                @error('name') <span class="text-xs text-[var(--accent-orange)]">{{ $message }}</span> @enderror
            </div>

            <!-- Business Model -->
            <div>
                <label class="block text-sm text-[var(--text-primary)] mb-2">Business Model</label>
                <div class="relative">
                    <select
                        wire:model="business_model"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] appearance-none cursor-pointer"
                    >
                        <option value="">Select business model</option>
                        <option value="b2b_saas">B2B SaaS</option>
                        <option value="b2c">B2C</option>
                        <option value="marketplace">Marketplace</option>
                        <option value="agency">Agency / Services</option>
                        <option value="other">Other</option>
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-secondary)] pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                @error('business_model') <span class="text-xs text-[var(--accent-orange)]">{{ $message }}</span> @enderror
            </div>

            <!-- Team Size Grid -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-[var(--text-primary)] mb-2">Co-founders</label>
                    <input
                        type="number"
                        wire:model="team_cofounders"
                        min="0"
                        placeholder="e.g. 2"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)]"
                    />
                    @error('team_cofounders') <span class="text-xs text-[var(--accent-orange)]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-[var(--text-primary)] mb-2">Team Size</label>
                    <input
                        type="number"
                        wire:model="team_employees"
                        min="0"
                        placeholder="e.g. 8-12"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)]"
                    />
                    @error('team_employees') <span class="text-xs text-[var(--accent-orange)]">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Your Position -->
            <div>
                <label class="block text-sm text-[var(--text-primary)] mb-2">Your Position</label>
                <input
                    type="text"
                    wire:model="user_position"
                    placeholder="CEO, CTO, Product Lead, etc."
                    class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)]"
                />
                @error('user_position') <span class="text-xs text-[var(--accent-orange)]">{{ $message }}</span> @enderror
            </div>

            <div class="h-px bg-[var(--border)]"></div>

            <!-- Advanced Section -->
            <div x-data="{ showAdvanced: false }">
                <button 
                    type="button" 
                    @click="showAdvanced = !showAdvanced"
                    class="flex items-center gap-2 text-sm text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors"
                >
                    <svg 
                        class="w-4 h-4 transition-transform" 
                        :class="{ 'rotate-180': showAdvanced }"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    Advanced Options
                </button>
                
                <div x-show="showAdvanced" x-collapse class="mt-6 space-y-6">
                    <!-- Customer Profile -->
                    <div>
                        <label class="block text-sm text-[var(--text-primary)] mb-2">Customer Profile</label>
                        <textarea
                            wire:model="customer_profile"
                            rows="3"
                            placeholder="Who are your customers? What problems do they have?"
                            class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] resize-none"
                        ></textarea>
                        @error('customer_profile') <span class="text-xs text-[var(--accent-orange)]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Market Insights -->
                    <div>
                        <label class="block text-sm text-[var(--text-primary)] mb-2">Market Insights</label>
                        <textarea
                            wire:model="market_insights"
                            rows="3"
                            placeholder="What's unique about your market? Key trends or competitive dynamics?"
                            class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] resize-none"
                        ></textarea>
                        @error('market_insights') <span class="text-xs text-[var(--accent-orange)]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Additional Information -->
                    <div>
                        <label class="block text-sm text-[var(--text-primary)] mb-2">Additional Information</label>
                        <textarea
                            wire:model="additional_information"
                            rows="4"
                            placeholder="Any additional context..."
                            class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] resize-none"
                        ></textarea>
                        @error('additional_information') <span class="text-xs text-[var(--accent-orange)]">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4">
                <a
                    href="{{ route('home') }}"
                    wire:navigate
                    class="px-6 py-3 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] text-[var(--text-primary)] border border-[var(--border)] rounded-lg transition-colors text-sm"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-8 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors"
                >
                    Continue to Goals
                </button>
            </div>
        </form>
    </div>
</div>
