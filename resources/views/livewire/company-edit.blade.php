<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-2xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 mb-4">
                ← Back to home
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Company Information</h1>
            <p class="mt-1 text-sm text-gray-600">Tell us about your company to help axia provide better insights.</p>
        </div>

        <!-- Success/Error Messages -->
        @if (session()->has('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif
        
        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Mode Toggle -->
        <div class="mb-6 flex items-center justify-center gap-2 bg-gray-100 p-1 rounded-xl inline-flex">
            <button
                wire:click="$set('mode', 'manual')"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ $mode === 'manual' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
            >
                Manual Entry
            </button>
            <button
                wire:click="$set('mode', 'smart')"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ $mode === 'smart' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
            >
                Smart Setup
            </button>
        </div>

        @if($mode === 'manual')
            <!-- Manual Entry Mode -->
            <form wire:submit.prevent="save" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                
                @if($extracted)
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                        <p class="text-sm text-blue-800">✨ Information extracted! Review and edit as needed.</p>
                    </div>
                @endif

                <!-- Company Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Company name
                    </label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        placeholder="Acme Inc."
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                    />
                    @error('name') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <!-- Business Model -->
                <div class="mb-6">
                    <label for="business_model" class="block text-sm font-medium text-gray-700 mb-2">
                        Business model <span class="text-gray-400">(optional)</span>
                    </label>
                    <select
                        id="business_model"
                        wire:model="business_model"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                    >
                        <option value="">Select a model</option>
                        <option value="b2b_saas">B2B SaaS</option>
                        <option value="b2c">B2C</option>
                        <option value="marketplace">Marketplace</option>
                        <option value="agency">Agency</option>
                        <option value="other">Other</option>
                    </select>
                    @error('business_model') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <!-- Team Size -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="team_cofounders" class="block text-sm font-medium text-gray-700 mb-2">
                            Co-founders <span class="text-gray-400">(optional)</span>
                        </label>
                        <input
                            type="number"
                            id="team_cofounders"
                            wire:model="team_cofounders"
                            min="0"
                            placeholder="2"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                        />
                        @error('team_cofounders') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="team_employees" class="block text-sm font-medium text-gray-700 mb-2">
                            Employees <span class="text-gray-400">(optional)</span>
                        </label>
                        <input
                            type="number"
                            id="team_employees"
                            wire:model="team_employees"
                            min="0"
                            placeholder="8"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                        />
                        @error('team_employees') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Your Position -->
                <div class="mb-6">
                    <label for="user_position" class="block text-sm font-medium text-gray-700 mb-2">
                        Your position <span class="text-gray-400">(optional)</span>
                    </label>
                    <input
                        type="text"
                        id="user_position"
                        wire:model="user_position"
                        placeholder="CEO, CTO, Product Lead, etc."
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                    />
                    @error('user_position') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <!-- Website -->
                <div class="mb-6">
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                        Website <span class="text-gray-400">(optional)</span>
                    </label>
                    <input
                        type="url"
                        id="website"
                        wire:model="website"
                        placeholder="https://example.com"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                    />
                    @error('website') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <!-- Advanced Section -->
                <div class="border-t border-gray-200 pt-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Advanced (Optional)</h3>
                    
                    <!-- Customer Profile -->
                    <div class="mb-6">
                        <label for="customer_profile" class="block text-sm font-medium text-gray-700 mb-2">
                            Customer profile
                        </label>
                        <textarea
                            id="customer_profile"
                            wire:model="customer_profile"
                            rows="3"
                            placeholder="Who are your customers? What problems do they have?"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all resize-none"
                        ></textarea>
                        @error('customer_profile') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <!-- Market Insights -->
                    <div class="mb-6">
                        <label for="market_insights" class="block text-sm font-medium text-gray-700 mb-2">
                            Key market insights
                        </label>
                        <textarea
                            id="market_insights"
                            wire:model="market_insights"
                            rows="3"
                            placeholder="What's unique about your market? Key trends or competitive dynamics?"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all resize-none"
                        ></textarea>
                        @error('market_insights') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <!-- Additional Information -->
                    <div class="mb-6">
                        <label for="additional_information" class="block text-sm font-medium text-gray-700 mb-2">
                            Additional information
                        </label>
                        <textarea
                            id="additional_information"
                            wire:model="additional_information"
                            rows="3"
                            placeholder="Any additional context or information that doesn't fit in the fields above..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all resize-none"
                        ></textarea>
                        @error('additional_information') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button
                        type="submit"
                        class="flex-1 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 px-6 rounded-xl hover:from-rose-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all shadow-sm"
                    >
                        Save changes
                    </button>
                    <a
                        href="{{ route('home') }}"
                        wire:navigate
                        class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors text-center"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        @else
            <!-- Smart Setup Mode -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Tell us about your company</h2>
                <p class="text-sm text-gray-600 mb-6">Describe your company in your own words. AI will extract the structured information.</p>
                
                <!-- Example Buttons -->
                <div class="mb-4">
                    <p class="text-xs font-medium text-gray-500 mb-2">Quick examples:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(\App\Services\ExampleContentService::getCompanyExamples() as $index => $example)
                            <button
                                type="button"
                                wire:click="insertExample({{ $index }})"
                                class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors"
                            >
                                {{ $example['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
                
                <textarea
                    wire:model="smartText"
                    rows="8"
                    placeholder="Example: We're a B2B SaaS startup with 2 co-founders and 8 employees. I'm the CEO. We build an AI coaching tool for early-stage founders who struggle with prioritization. Our customers are startup CEOs in tech. Our website is acme.ai..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all resize-none text-sm"
                    @disabled($extracting)
                ></textarea>

                <div class="mt-6">
                    <button
                        wire:click="extractInfo"
                        @disabled($extracting)
                        class="w-full bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 px-6 rounded-xl hover:from-rose-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        @if($extracting)
                            <span class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Extracting information...
                            </span>
                        @else
                            Extract company info
                        @endif
                    </button>
                </div>

                <p class="mt-4 text-xs text-center text-gray-500">
                    AI will analyze your text and fill the form automatically
                </p>
            </div>
        @endif
        </div>
    </div>
</div>
