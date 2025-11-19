<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-3xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 mb-4">
                ← Back to home
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Goals & KPIs</h1>
            <p class="mt-1 text-sm text-gray-600">Define what success looks like for your company.</p>
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
            <form wire:submit.prevent="save">
                
                @if($extracted)
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                        <p class="text-sm text-blue-800">✨ Goals & KPIs extracted! Review and edit as needed.</p>
                    </div>
                @endif

                <!-- Goals with KPIs -->
                @if(!empty($goals))
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Goals with KPIs</h2>
                        <div class="space-y-6">
                            @foreach($goals as $goalIndex => $goal)
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                                    
                                    <div class="flex items-start justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900">Goal {{ $goalIndex + 1 }}</h3>
                                        @if(count($goals) > 1)
                                            <button type="button" wire:click="removeGoal({{ $goalIndex }})" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                                Remove
                                            </button>
                                        @endif
                                    </div>

                                    <div class="mb-4">
                                        <input type="text" wire:model="goals.{{ $goalIndex }}.title" placeholder="Goal title" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"/>
                                    </div>

                                    <!-- Additional Information for Goal -->
                                    <div class="mb-4">
                                        <textarea
                                            wire:model="goals.{{ $goalIndex }}.additional_information"
                                            rows="2"
                                            placeholder="Additional information about this goal..."
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none"
                                        ></textarea>
                                    </div>

                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="text-sm font-medium text-gray-700">KPIs</h4>
                                            <button type="button" wire:click="addKpi({{ $goalIndex }})" class="text-sm font-medium text-rose-600 hover:text-rose-700">+ Add KPI</button>
                                        </div>

                                        @if(empty($goal['kpis']))
                                            <p class="text-sm text-gray-500 italic">No KPIs yet</p>
                                        @else
                                            <div class="space-y-4">
                                                @foreach($goal['kpis'] as $kpiIndex => $kpi)
                                                    <div class="bg-gray-50 rounded-xl p-4">
                                                        <div class="flex items-start justify-between mb-3">
                                                            <span class="text-sm font-medium text-gray-700">KPI {{ $kpiIndex + 1 }}</span>
                                                            <div class="flex items-center gap-2">
                                                                @if($kpi['is_top_kpi'])
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-rose-100 text-rose-700 text-xs font-medium">Top KPI</span>
                                                                @else
                                                                    <button type="button" wire:click="setTopKpi({{ $goalIndex }}, {{ $kpiIndex }})" class="text-xs font-medium text-gray-600 hover:text-rose-600">Set as top</button>
                                                                @endif
                                                                <button type="button" wire:click="removeKpi({{ $goalIndex }}, {{ $kpiIndex }})" class="text-red-600 hover:text-red-700 text-xs font-medium">Remove</button>
                                                            </div>
                                                        </div>
                                                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                                            <input type="text" wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.name" placeholder="KPI name" class="col-span-1 sm:col-span-4 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                                            <input type="number" step="0.01" wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.current_value" placeholder="Current" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                                            <input type="number" step="0.01" wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.target_value" placeholder="Target" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                                            <input type="text" wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.unit" placeholder="Unit" class="col-span-2 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                                        </div>
                                                        <!-- Additional Information for KPI -->
                                                        <div class="mt-3">
                                                            <textarea
                                                                wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.additional_information"
                                                                rows="2"
                                                                placeholder="Additional information about this KPI..."
                                                                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none"
                                                            ></textarea>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" wire:click="addGoal" class="w-full py-3 px-4 border-2 border-dashed border-gray-300 rounded-xl text-sm font-medium text-gray-600 hover:border-rose-500 hover:text-rose-600 transition-colors">+ Add another goal</button>
                    </div>
                @endif

                <!-- Standalone KPIs -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Standalone KPIs</h2>
                            <p class="text-sm text-gray-600 mt-1">Direct metrics not tied to a goal</p>
                        </div>
                        <button type="button" wire:click="addStandaloneKpi" class="px-4 py-2 text-sm font-medium text-rose-600 hover:text-rose-700 bg-rose-50 rounded-lg">+ Add KPI</button>
                    </div>

                    @if(empty($standaloneKpis))
                        <div class="bg-gray-50 rounded-xl p-6 text-center">
                            <p class="text-sm text-gray-500">No standalone KPIs yet</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($standaloneKpis as $kpiIndex => $kpi)
                                <div class="bg-white rounded-xl border border-gray-200 p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <span class="text-sm font-medium text-gray-700">KPI {{ $kpiIndex + 1 }}</span>
                                        <div class="flex items-center gap-2">
                                            @if($kpi['is_top_kpi'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-rose-100 text-rose-700 text-xs font-medium">Top KPI</span>
                                            @else
                                                <button type="button" wire:click="setTopKpiStandalone({{ $kpiIndex }})" class="text-xs font-medium text-gray-600 hover:text-rose-600">Set as top</button>
                                            @endif
                                            <button type="button" wire:click="removeStandaloneKpi({{ $kpiIndex }})" class="text-red-600 hover:text-red-700 text-xs font-medium">Remove</button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                        <input type="text" wire:model="standaloneKpis.{{ $kpiIndex }}.name" placeholder="KPI name" class="col-span-1 sm:col-span-4 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                        <input type="number" step="0.01" wire:model="standaloneKpis.{{ $kpiIndex }}.current_value" placeholder="Current" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                        <input type="number" step="0.01" wire:model="standaloneKpis.{{ $kpiIndex }}.target_value" placeholder="Target" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                        <input type="text" wire:model="standaloneKpis.{{ $kpiIndex }}.unit" placeholder="Unit" class="col-span-2 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"/>
                                    </div>
                                    <!-- Additional Information for Standalone KPI -->
                                    <div class="mt-3">
                                        <textarea
                                            wire:model="standaloneKpis.{{ $kpiIndex }}.additional_information"
                                            rows="2"
                                            placeholder="Additional information about this KPI..."
                                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none"
                                        ></textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 px-6 rounded-xl hover:from-rose-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all shadow-sm">
                        Save goals & KPIs
                    </button>
                    <a href="{{ route('home') }}" wire:navigate class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors text-center">
                        Cancel
                    </a>
                </div>
            </form>
        @else
            <!-- Smart Setup Mode -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">What are you trying to achieve?</h2>
                <p class="text-sm text-gray-600 mb-6">Describe your goals and metrics. AI will structure them for you.</p>
                
                <!-- Example Buttons -->
                <div class="mb-4">
                    <p class="text-xs font-medium text-gray-500 mb-2">Quick examples:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(\App\Services\ExampleContentService::getGoalsExamples() as $index => $example)
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
                    rows="10"
                    placeholder="Example: We want to reach product-market fit by end of Q2. Our main metric is getting to 1000 monthly active users. We also need to hire 2 senior engineers and raise our seed round of 500k EUR. Current MRR is 5k, aiming for 50k by year end..."
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
                                Generating goals & KPIs...
                            </span>
                        @else
                            Generate goals & KPIs
                        @endif
                    </button>
                </div>

                <p class="mt-4 text-xs text-center text-gray-500">
                    AI will identify your goals, KPIs, and automatically mark the most important one
                </p>
            </div>
        @endif
        </div>
    </div>
</div>
