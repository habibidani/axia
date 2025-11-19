<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-2xl mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Welcome to axia! 👋</h1>
            <p class="text-gray-600">Let's set up your profile and goals to get personalized insights.</p>
        </div>

        <!-- Step Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center gap-2">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $step >= 1 ? 'bg-rose-500 text-white' : 'bg-gray-200 text-gray-600' }} text-sm font-semibold">1</div>
                    <span class="ml-3 text-sm {{ $step === 1 ? 'text-gray-900 font-medium' : 'text-gray-500' }}">Profile</span>
                </div>
                <div class="w-12 h-0.5 {{ $step > 1 ? 'bg-rose-500' : 'bg-gray-200' }}"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $step >= 2 ? 'bg-rose-500 text-white' : 'bg-gray-200 text-gray-600' }} text-sm font-semibold">2</div>
                    <span class="ml-3 text-sm {{ $step === 2 ? 'text-gray-900 font-medium' : 'text-gray-500' }}">Company</span>
                </div>
                <div class="w-12 h-0.5 {{ $step > 2 ? 'bg-rose-500' : 'bg-gray-200' }}"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $step >= 3 ? 'bg-rose-500 text-white' : 'bg-gray-200 text-gray-600' }} text-sm font-semibold">3</div>
                    <span class="ml-3 text-sm {{ $step === 3 ? 'text-gray-900 font-medium' : 'text-gray-500' }}">Goals</span>
                </div>
            </div>
        </div>

        <!-- Content Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            
            <!-- Step 1: Profile -->
            @if($step === 1)
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Your Profile</h2>
                    <p class="text-sm text-gray-600 mb-6">Tell us a bit about yourself</p>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First name</label>
                            <input type="text" wire:model="first_name" placeholder="John" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last name</label>
                            <input type="text" wire:model="last_name" placeholder="Doe" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"/>
                        </div>
                        @if(!auth()->user()->is_guest)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" wire:model="email" placeholder="john@example.com" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"/>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Step 2: Company -->
            @if($step === 2)
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Company</h2>
                            <p class="text-sm text-gray-600 mt-1">Tell us about your company</p>
                        </div>
                        <div class="flex gap-2 bg-gray-100 p-1 rounded-lg">
                            <button type="button" wire:click="$set('companyMode', 'manual')" class="px-3 py-1.5 text-xs font-medium rounded transition-all {{ $companyMode === 'manual' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}">Manual</button>
                            <button type="button" wire:click="$set('companyMode', 'smart')" class="px-3 py-1.5 text-xs font-medium rounded transition-all {{ $companyMode === 'smart' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}">Smart</button>
                        </div>
                    </div>
                    
                    @if($companyMode === 'manual')
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Company name</label>
                                <input type="text" wire:model="name" placeholder="Acme Inc." class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"/>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Business model</label>
                                <select wire:model="business_model" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all">
                                    <option value="">Select</option>
                                    <option value="b2b_saas">B2B SaaS</option>
                                    <option value="b2c">B2C</option>
                                    <option value="marketplace">Marketplace</option>
                                    <option value="agency">Agency</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Co-founders</label>
                                    <input type="number" wire:model="team_cofounders" min="0" placeholder="2" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Employees</label>
                                    <input type="number" wire:model="team_employees" min="0" placeholder="8" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Your role</label>
                                    <input type="text" wire:model="user_position" placeholder="CEO" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"/>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Additional information</label>
                                <textarea wire:model="companyAdditionalInformation" rows="3" placeholder="Any additional context about your company..." class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none"></textarea>
                            </div>
                        </div>
                    @else
                        <div class="space-y-4">
                            <textarea wire:model="companySmartText" rows="6" placeholder="Example: We're a B2B SaaS with 2 co-founders and 8 employees. I'm the CEO..." class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none text-sm" @disabled($companyExtracting)></textarea>
                            <button type="button" wire:click="extractCompanyInfo" @disabled($companyExtracting) class="w-full bg-rose-500 text-white font-medium py-2.5 px-4 rounded-lg hover:bg-rose-600 transition-colors disabled:opacity-50">
                                {{ $companyExtracting ? 'Extracting...' : 'Extract info' }}
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Step 3: Goals -->
            @if($step === 3)
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Goals & KPIs</h2>
                            <p class="text-sm text-gray-600 mt-1">What are you trying to achieve?</p>
                        </div>
                        <div class="flex gap-2 bg-gray-100 p-1 rounded-lg">
                            <button type="button" wire:click="$set('goalsMode', 'manual')" class="px-3 py-1.5 text-xs font-medium rounded transition-all {{ $goalsMode === 'manual' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}">Manual</button>
                            <button type="button" wire:click="$set('goalsMode', 'smart')" class="px-3 py-1.5 text-xs font-medium rounded transition-all {{ $goalsMode === 'smart' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}">Smart</button>
                        </div>
                    </div>
                    
                    @if($goalsMode === 'manual')
                        <div class="space-y-6">
                            @foreach($goals as $goalIndex => $goal)
                                <div class="border border-gray-200 rounded-xl p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <h3 class="text-sm font-semibold text-gray-900">Goal {{ $goalIndex + 1 }}</h3>
                                        @if(count($goals) > 1)
                                            <button type="button" wire:click="removeGoal({{ $goalIndex }})" class="text-red-600 hover:text-red-700 text-sm font-medium">Remove</button>
                                        @endif
                                    </div>
                                    <div class="mb-4">
                                        <input type="text" wire:model="goals.{{ $goalIndex }}.title" placeholder="Goal title" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"/>
                                    </div>
                                    <div class="mb-4">
                                        <textarea wire:model="goals.{{ $goalIndex }}.additional_information" rows="2" placeholder="Additional information about this goal..." class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none"></textarea>
                                    </div>
                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="text-sm font-medium text-gray-700">KPIs</h4>
                                            <button type="button" wire:click="addKpi({{ $goalIndex }})" class="text-sm font-medium text-rose-600 hover:text-rose-700">+ Add KPI</button>
                                        </div>
                                        @if(empty($goal['kpis']))
                                            <p class="text-sm text-gray-500 italic">No KPIs yet</p>
                                        @else
                                            <div class="space-y-3">
                                                @foreach($goal['kpis'] as $kpiIndex => $kpi)
                                                    <div class="bg-gray-50 rounded-lg p-4">
                                                        <div class="flex items-start justify-between mb-3">
                                                            <span class="text-xs font-medium text-gray-600">KPI {{ $kpiIndex + 1 }}</span>
                                                            <div class="flex items-center gap-2">
                                                                @if($kpi['is_top_kpi'])
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-rose-100 text-rose-700 text-xs font-medium">Top KPI</span>
                                                                @else
                                                                    <button type="button" wire:click="setTopKpi({{ $goalIndex }}, {{ $kpiIndex }})" class="text-xs text-gray-600 hover:text-rose-600">Set as top</button>
                                                                @endif
                                                                <button type="button" wire:click="removeKpi({{ $goalIndex }}, {{ $kpiIndex }})" class="text-xs text-red-600 hover:text-red-700">Remove</button>
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                                            <input type="text" wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.name" placeholder="KPI name" class="col-span-2 sm:col-span-4 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                                            <input type="number" step="0.01" wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.current_value" placeholder="Current" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                                            <input type="number" step="0.01" wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.target_value" placeholder="Target" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                                            <input type="text" wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.unit" placeholder="Unit" class="col-span-2 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                                        </div>
                                                        <div class="mt-3">
                                                            <textarea wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.additional_information" rows="2" placeholder="Additional information about this KPI..." class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none"></textarea>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <button type="button" wire:click="addGoal" class="w-full mt-4 py-3 px-4 border-2 border-dashed border-gray-300 rounded-xl text-sm font-medium text-gray-600 hover:border-rose-500 hover:text-rose-600 transition-colors">+ Add another goal</button>
                        </div>
                    @else
                        <div class="space-y-4">
                            <textarea wire:model="goalsSmartText" rows="8" placeholder="Example: Reach 1000 users by Q2. Hire 2 engineers. Raise 500k..." class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none text-sm" @disabled($goalsExtracting)></textarea>
                            <button type="button" wire:click="extractGoalsInfo" @disabled($goalsExtracting) class="w-full bg-rose-500 text-white font-medium py-2.5 px-4 rounded-lg hover:bg-rose-600 transition-colors disabled:opacity-50">
                                {{ $goalsExtracting ? 'Generating...' : 'Generate goals & KPIs' }}
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-6 flex items-center gap-3">
            @if($step > 1)
                <button type="button" wire:click="previousStep" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">← Back</button>
            @endif
            <div class="flex-1 flex gap-3">
                @if($step < 3)
                    <button type="button" wire:click="nextStep" class="flex-1 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 px-6 rounded-xl hover:from-rose-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all shadow-sm">Continue →</button>
                @else
                    <button type="button" wire:click="save" class="flex-1 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 px-6 rounded-xl hover:from-rose-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all shadow-sm">Complete Setup</button>
                @endif
                <button type="button" wire:click="skipStep" class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">Skip {{ $step < 3 ? 'this step' : 'for now' }}</button>
            </div>
        </div>

        <p class="text-center text-sm text-gray-500 mt-4">You can always update this later in Settings</p>
        </div>
    </div>
</div>
