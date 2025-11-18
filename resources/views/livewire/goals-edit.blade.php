<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-3xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 mb-4">
                ← Back to home
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Goals & KPIs</h1>
            <p class="mt-1 text-sm text-gray-600">Define your objectives and key performance indicators to help axia prioritize your tasks.</p>
        </div>

        <!-- Success Message -->
        @if (session()->has('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Form -->
        <form wire:submit.prevent="save">
            
            <!-- Goals -->
            <div class="space-y-6 mb-6">
                @foreach($goals as $goalIndex => $goal)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        
                        <!-- Goal Header -->
                        <div class="flex items-start justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Goal {{ $goalIndex + 1 }}</h3>
                            @if(count($goals) > 1)
                                <button
                                    type="button"
                                    wire:click="removeGoal({{ $goalIndex }})"
                                    class="text-red-600 hover:text-red-700 text-sm font-medium"
                                >
                                    Remove
                                </button>
                            @endif
                        </div>

                        <!-- Goal Title -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Title <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="goals.{{ $goalIndex }}.title"
                                placeholder="e.g., Reach product-market fit"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                            />
                        </div>

                        <!-- Goal Description -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description <span class="text-gray-400">(optional)</span>
                            </label>
                            <textarea
                                wire:model="goals.{{ $goalIndex }}.description"
                                rows="2"
                                placeholder="Additional context about this goal"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all resize-none"
                            ></textarea>
                        </div>

                        <!-- Priority and Time Frame -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Priority <span class="text-gray-400">(optional)</span>
                                </label>
                                <select
                                    wire:model="goals.{{ $goalIndex }}.priority"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                                >
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Time frame <span class="text-gray-400">(optional)</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="goals.{{ $goalIndex }}.time_frame"
                                    placeholder="e.g., Q1 2024, Next 6 months"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                                />
                            </div>
                        </div>

                        <!-- KPIs Section -->
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-semibold text-gray-900">KPIs for this goal</h4>
                                <button
                                    type="button"
                                    wire:click="addKpi({{ $goalIndex }})"
                                    class="text-sm font-medium text-rose-600 hover:text-rose-700"
                                >
                                    + Add KPI
                                </button>
                            </div>

                            @if(empty($goal['kpis']))
                                <p class="text-sm text-gray-500 italic">No KPIs yet. Add one to get started.</p>
                            @else
                                <div class="space-y-4">
                                    @foreach($goal['kpis'] as $kpiIndex => $kpi)
                                        <div class="bg-gray-50 rounded-xl p-4">
                                            
                                            <div class="flex items-start justify-between mb-3">
                                                <span class="text-sm font-medium text-gray-700">KPI {{ $kpiIndex + 1 }}</span>
                                                <div class="flex items-center gap-2">
                                                    @if($kpi['is_top_kpi'])
                                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-rose-100 text-rose-700 text-xs font-medium">
                                                            Top KPI
                                                        </span>
                                                    @else
                                                        <button
                                                            type="button"
                                                            wire:click="setTopKpi({{ $goalIndex }}, {{ $kpiIndex }})"
                                                            class="text-xs font-medium text-gray-600 hover:text-rose-600"
                                                        >
                                                            Set as top KPI
                                                        </button>
                                                    @endif
                                                    <button
                                                        type="button"
                                                        wire:click="removeKpi({{ $goalIndex }}, {{ $kpiIndex }})"
                                                        class="text-red-600 hover:text-red-700 text-xs font-medium"
                                                    >
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- KPI Name -->
                                            <div class="mb-3">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                                    Name <span class="text-red-500">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.name"
                                                    placeholder="e.g., Monthly Active Users"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                                                />
                                            </div>

                                            <!-- Current, Target, Unit -->
                                            <div class="grid grid-cols-3 gap-3 mb-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                                        Current <span class="text-gray-400">(optional)</span>
                                                    </label>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.current_value"
                                                        placeholder="100"
                                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                                                    />
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                                        Target <span class="text-gray-400">(optional)</span>
                                                    </label>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.target_value"
                                                        placeholder="500"
                                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                                                    />
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                                        Unit <span class="text-gray-400">(optional)</span>
                                                    </label>
                                                    <input
                                                        type="text"
                                                        wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.unit"
                                                        placeholder="users, €, %"
                                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                                                    />
                                                </div>
                                            </div>

                                            <!-- Time Frame -->
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                                    Time frame <span class="text-gray-400">(optional)</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="goals.{{ $goalIndex }}.kpis.{{ $kpiIndex }}.time_frame"
                                                    placeholder="e.g., by end of Q2"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all"
                                                />
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Add Goal Button -->
            <button
                type="button"
                wire:click="addGoal"
                class="w-full mb-6 py-3 px-4 border-2 border-dashed border-gray-300 rounded-xl text-sm font-medium text-gray-600 hover:border-rose-500 hover:text-rose-600 transition-colors"
            >
                + Add another goal
            </button>

            <!-- Actions -->
            <div class="flex gap-3">
                <button
                    type="submit"
                    class="flex-1 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 px-6 rounded-xl hover:from-rose-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all shadow-sm"
                >
                    Save goals & KPIs
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
        </div>
    </div>
</div>


