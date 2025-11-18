<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Company Info -->
                <div>
                    @if($run->company)
                        <h2 class="text-lg font-bold text-gray-900">{{ $run->company->name ?? 'Company' }}</h2>
                        <div class="mt-1 text-sm text-gray-600">
                            @if($run->company->business_model)
                                <span class="capitalize">{{ str_replace('_', ' ', $run->company->business_model) }}</span>
                            @endif
                            @if($run->company->team_cofounders || $run->company->team_employees)
                                <br>
                                <span>{{ $run->company->team_cofounders ?? 0 }} founders · {{ $run->company->team_employees ?? 0 }} employees</span>
                            @endif
                        </div>
                    @else
                        <h2 class="text-lg font-bold text-gray-900">Guest Analysis</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ $run->created_at->format('F j, Y') }}</p>
                    @endif
                </div>

                <!-- Overall Score -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-rose-500 to-pink-500 text-white mb-2">
                        <span class="text-3xl font-bold">{{ $run->overall_score ?? '—' }}</span>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Focus Score</h3>
                    @if($run->summary_text)
                        <p class="mt-2 text-sm text-gray-600">{{ $run->summary_text }}</p>
                    @endif
                </div>

                <!-- Top KPI -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-gray-900">Top KPI</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-rose-100 text-rose-700 text-xs font-medium">
                            Primary
                        </span>
                    </div>
                    @if($run->snapshotTopKpi)
                        <h4 class="text-sm font-bold text-gray-900 mb-2">{{ $run->snapshotTopKpi->name }}</h4>
                        <div class="text-xs text-gray-600">
                            <div>Current: {{ number_format($run->snapshotTopKpi->current_value, 0) }} {{ $run->snapshotTopKpi->unit }}</div>
                            <div>Target: {{ number_format($run->snapshotTopKpi->target_value, 0) }} {{ $run->snapshotTopKpi->unit }}</div>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">No top KPI set</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tasks Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Your tasks ranked by impact on your goals</h2>

            <div class="space-y-3">
                @forelse($sortedTodos as $todo)
                    @php
                        $evaluation = $todo->evaluation;
                        $isExpanded = in_array($todo->id, $expandedTasks);
                        
                        $colorClasses = [
                            'green' => 'bg-green-500',
                            'yellow' => 'bg-yellow-500',
                            'orange' => 'bg-orange-500',
                        ];
                        
                        $iconMap = [
                            'high' => '↑',
                            'delegate' => '👤',
                            'drop' => '−',
                        ];
                    @endphp

                    <div class="border border-gray-200 rounded-xl overflow-hidden transition-all hover:border-gray-300">
                        <!-- Compact Row -->
                        <div 
                            class="flex items-center gap-4 p-4 cursor-pointer"
                            wire:click="toggleTask('{{ $todo->id }}')"
                        >
                            <!-- Color Dot -->
                            <div class="flex-shrink-0">
                                <div class="w-3 h-3 rounded-full {{ $colorClasses[$evaluation->color] ?? 'bg-gray-400' }}"></div>
                            </div>

                            <!-- Score -->
                            <div class="flex-shrink-0 w-12 text-center">
                                <span class="text-lg font-bold text-gray-900">{{ $evaluation->score }}</span>
                            </div>

                            <!-- Task Title -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $todo->normalized_title }}</p>
                            </div>

                            <!-- Recommendation Icons -->
                            <div class="flex-shrink-0 flex items-center gap-2">
                                @if($evaluation->priority_recommendation === 'high')
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-green-100 text-green-700 text-sm font-bold" title="High priority">
                                        ↑
                                    </span>
                                @endif
                                @if($evaluation->action_recommendation === 'delegate')
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-yellow-100 text-yellow-700 text-sm" title="Delegate">
                                        👤
                                    </span>
                                @endif
                                @if($evaluation->action_recommendation === 'drop')
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-orange-100 text-orange-700 text-sm font-bold" title="Drop">
                                        −
                                    </span>
                                @endif
                            </div>

                            <!-- Chevron -->
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400 transition-transform {{ $isExpanded ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Expanded Details -->
                        @if($isExpanded)
                            <div class="border-t border-gray-200 bg-gray-50 p-4">
                                <div class="space-y-3">
                                    <!-- Reasoning -->
                                    <div>
                                        <h4 class="text-xs font-semibold text-gray-700 mb-1">Reasoning</h4>
                                        <p class="text-sm text-gray-900">{{ $evaluation->reasoning }}</p>
                                    </div>

                                    <!-- Linked Goal/KPI -->
                                    @if($evaluation->primaryGoal || $evaluation->primaryKpi)
                                        <div>
                                            <h4 class="text-xs font-semibold text-gray-700 mb-1">Linked to</h4>
                                            <div class="text-sm text-gray-900">
                                                @if($evaluation->primaryGoal)
                                                    <span class="inline-flex items-center px-2 py-1 rounded bg-blue-50 text-blue-700 text-xs font-medium mr-2">
                                                        Goal: {{ $evaluation->primaryGoal->title }}
                                                    </span>
                                                @endif
                                                @if($evaluation->primaryKpi)
                                                    <span class="inline-flex items-center px-2 py-1 rounded bg-purple-50 text-purple-700 text-xs font-medium">
                                                        KPI: {{ $evaluation->primaryKpi->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Recommendation -->
                                    <div>
                                        <h4 class="text-xs font-semibold text-gray-700 mb-1">Recommendation</h4>
                                        <p class="text-sm text-gray-900">
                                            @if($evaluation->priority_recommendation === 'high')
                                                <span class="font-semibold text-green-700">Prioritize (high)</span>
                                            @elseif($evaluation->priority_recommendation === 'low')
                                                <span class="font-semibold text-orange-700">Low priority</span>
                                            @endif

                                            @if($evaluation->action_recommendation === 'delegate' && $evaluation->delegation_target_role)
                                                — Delegate to {{ $evaluation->delegation_target_role }}
                                            @elseif($evaluation->action_recommendation === 'drop')
                                                — Drop (low impact on current goals)
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-8">No tasks to display</p>
                @endforelse
            </div>
        </div>

        <!-- What's Missing Section -->
        @if($run->missingTodos->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">What's missing?</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($run->missingTodos as $missing)
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-sm font-semibold text-gray-900">{{ $missing->title }}</h3>
                                @if($missing->category)
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-rose-50 text-rose-700 text-xs font-medium capitalize">
                                        {{ $missing->category }}
                                    </span>
                                @endif
                            </div>
                            
                            @if($missing->description)
                                <p class="text-sm text-gray-600 mb-3">{{ $missing->description }}</p>
                            @endif

                            <div class="flex items-center justify-between text-xs text-gray-500">
                                @if($missing->impact_score)
                                    <span>Impact: {{ $missing->impact_score }}/100</span>
                                @endif
                                @if($missing->suggested_owner_role)
                                    <span>Owner: {{ $missing->suggested_owner_role }}</span>
                                @endif
                            </div>

                            @if($missing->goal || $missing->kpi)
                                <div class="mt-2 pt-2 border-t border-gray-200">
                                    @if($missing->goal)
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-blue-50 text-blue-700 text-xs font-medium mr-1">
                                            {{ $missing->goal->title }}
                                        </span>
                                    @endif
                                    @if($missing->kpi)
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-purple-50 text-purple-700 text-xs font-medium">
                                            {{ $missing->kpi->name }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3">
            <button
                wire:click="exportCsv"
                class="flex-1 bg-white text-gray-700 font-semibold py-3 px-6 rounded-xl border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all"
            >
                Export as CSV
            </button>
            <a
                href="{{ route('home') }}"
                wire:navigate
                class="flex-1 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-semibold py-3 px-6 rounded-xl hover:from-rose-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all shadow-sm text-center"
            >
                Back to home
            </a>
        </div>
    </div>
</div>


