<div class="max-w-[1400px] mx-auto px-8 py-12">
    
    <!-- TOP COMPONENT - 3 Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        
        <!-- Left: Company Info -->
        <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
            <div class="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">Company Info</div>
            @if($run->company)
                <div class="space-y-3">
                    <div>
                        <div class="text-xs text-[var(--text-secondary)] mb-1">Name</div>
                        <div class="text-sm text-[var(--text-primary)]">{{ $run->company->name ?? 'Not set' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-[var(--text-secondary)] mb-1">Model</div>
                        <div class="text-sm text-[var(--text-primary)] capitalize">{{ $run->company->business_model ? str_replace('_', ' ', $run->company->business_model) : 'Not set' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-[var(--text-secondary)] mb-1">Team Size</div>
                        <div class="text-sm text-[var(--text-primary)]">{{ ($run->company->team_cofounders ?? 0) + ($run->company->team_employees ?? 0) }} people</div>
                    </div>
                </div>
            @else
                <p class="text-sm text-[var(--text-secondary)]">Guest Analysis • {{ $run->created_at->format('M d, Y') }}</p>
            @endif
        </div>

        <!-- Center: Focus Score -->
        @php
            $focusScore = $run->overall_score ?? 0;
            $scoreColor = $focusScore >= 70 ? '#4CAF50' : ($focusScore >= 50 ? '#FFB74D' : '#FF8A65');
        @endphp
        <div class="flex flex-col items-center justify-center">
            <div 
                class="w-40 h-40 rounded-full flex items-center justify-center mb-4"
                style="border: 6px solid {{ $scoreColor }}30; background-color: {{ $scoreColor }}05;"
            >
                <div class="text-center">
                    <div class="text-5xl text-[var(--text-primary)] mb-1 font-medium">{{ $focusScore }}</div>
                    <div class="text-xs text-[var(--text-secondary)]">/100</div>
                </div>
            </div>
            <div class="text-sm text-[var(--text-secondary)]">Focus Score</div>
        </div>

        <!-- Right: High-Impact Goals -->
        <div class="bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
            <div class="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">High-Impact Goals</div>
            @if($run->company && $run->company->goals->where('priority', 'high')->count() > 0)
                <div class="space-y-4">
                    @foreach($run->company->goals->where('priority', 'high')->take(3) as $goal)
                        <div>
                            <div class="text-sm text-[var(--text-primary)] mb-1">{{ $goal->normalized_title }}</div>
                            @if($goal->kpis->where('is_top_kpi', true)->first())
                                <div class="text-xs text-[var(--accent-pink)]">⭐ {{ $goal->kpis->where('is_top_kpi', true)->first()->name }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-[var(--text-secondary)]">No high-priority goals set</p>
            @endif
        </div>
    </div>

    <!-- SCORE SUMMARY SECTION -->
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)] mb-12">
        <h2 class="text-[var(--text-primary)] mb-4">Summary of Your Focus Score</h2>
        <div class="space-y-4 text-[var(--text-secondary)]">
            @if($run->summary_text)
                <p>{{ $run->summary_text }}</p>
            @else
                <p>
                    Your focus score of {{ $focusScore }} indicates 
                    @if($focusScore >= 70)
                        a strong alignment between your to-do list and strategic goals. You're prioritizing effectively!
                    @elseif($focusScore >= 50)
                        a moderate level of alignment between your to-do list and strategic goals. There's room for improvement.
                    @else
                        limited alignment between your current tasks and your top goals. Consider refocusing your priorities.
                    @endif
                </p>
                <p>
                    The analysis below shows which tasks deserve your immediate attention and which can wait or be delegated.
                </p>
            @endif
        </div>
    </div>

    <!-- TASK ACCORDION SECTION -->
    @php
        // Group tasks by impact
        $highTasks = $sortedTodos->filter(fn($t) => ($t->evaluation?->score ?? 0) >= 70);
        $midTasks = $sortedTodos->filter(fn($t) => ($t->evaluation?->score ?? 0) >= 50 && ($t->evaluation?->score ?? 0) < 70);
        $lowTasks = $sortedTodos->filter(fn($t) => ($t->evaluation?->score ?? 0) < 50);
        
        $impactColors = [
            'high' => '#4CAF50',
            'mid' => '#FFB74D',
            'low' => '#FF8A65',
        ];
    @endphp
    
    <div class="mb-12">
        <h2 class="text-[var(--text-primary)] mb-8">Your Tasks by Impact</h2>

        <!-- High Impact -->
        @if($highTasks->count() > 0)
            <div class="mb-10">
                <div class="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">High Impact</div>
                <div class="space-y-3">
                    @foreach($highTasks as $todo)
                        @php
                            $evaluation = $todo->evaluation;
                            $isExpanded = in_array($todo->id, $expandedTasks);
                        @endphp
                        <div class="bg-[var(--bg-secondary)] rounded-xl border border-[var(--border)] overflow-hidden">
                            <!-- Accordion Header -->
                            <button
                                wire:click="toggleTask('{{ $todo->id }}')"
                                class="w-full flex items-center gap-4 p-5 hover:bg-[var(--bg-hover)] transition-colors text-left"
                            >
                                <div class="w-1 h-12 rounded-full shrink-0" style="background-color: {{ $impactColors['high'] }};"></div>
                                <div class="flex-1">
                                    <div class="text-[var(--text-primary)] text-sm">{{ $todo->normalized_title }}</div>
                                </div>
                                <span class="px-3 py-1 rounded-lg text-xs border shrink-0" style="background-color: {{ $impactColors['high'] }}10; color: {{ $impactColors['high'] }}; border-color: {{ $impactColors['high'] }}30;">
                                    High
                                </span>
                                <span class="px-3 py-1 rounded-lg text-xs bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border border-[var(--border)] shrink-0">
                                    Score: {{ $evaluation?->score ?? 0 }}
                                </span>
                                <svg class="w-5 h-5 text-[var(--text-secondary)] transition-transform {{ $isExpanded ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Accordion Content -->
                            @if($isExpanded && $evaluation)
                                <div class="px-5 pb-8 pt-6 border-t border-[var(--border)]">
                                    <div class="pl-5 space-y-6">
                                        <p class="text-sm text-[var(--text-secondary)] leading-relaxed">
                                            {{ $evaluation->reasoning }}
                                        </p>

                                        <div class="flex flex-wrap gap-2 pt-2">
                                            @if($evaluation->primaryGoal)
                                                <span class="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                                                    {{ $evaluation->primaryGoal->normalized_title }}
                                                </span>
                                            @endif
                                            @if($evaluation->priority_recommendation)
                                                <span class="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                                                    {{ ucfirst($evaluation->priority_recommendation) }} priority
                                                </span>
                                            @endif
                                            @if($evaluation->action_recommendation === 'delegate' && $evaluation->delegation_target_role)
                                                <span class="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                                                    Delegate to {{ $evaluation->delegation_target_role }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Mid Impact -->
        @if($midTasks->count() > 0)
            <div class="mb-10">
                <div class="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">Mid Impact</div>
                <div class="space-y-3">
                    @foreach($midTasks as $todo)
                        @php
                            $evaluation = $todo->evaluation;
                            $isExpanded = in_array($todo->id, $expandedTasks);
                        @endphp
                        <div class="bg-[var(--bg-secondary)] rounded-xl border border-[var(--border)] overflow-hidden">
                            <button
                                wire:click="toggleTask('{{ $todo->id }}')"
                                class="w-full flex items-center gap-4 p-5 hover:bg-[var(--bg-hover)] transition-colors text-left"
                            >
                                <div class="w-1 h-12 rounded-full shrink-0" style="background-color: {{ $impactColors['mid'] }};"></div>
                                <div class="flex-1">
                                    <div class="text-[var(--text-primary)] text-sm">{{ $todo->normalized_title }}</div>
                                </div>
                                <span class="px-3 py-1 rounded-lg text-xs border shrink-0" style="background-color: {{ $impactColors['mid'] }}10; color: {{ $impactColors['mid'] }}; border-color: {{ $impactColors['mid'] }}30;">
                                    Mid
                                </span>
                                <span class="px-3 py-1 rounded-lg text-xs bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border border-[var(--border)] shrink-0">
                                    Score: {{ $evaluation?->score ?? 0 }}
                                </span>
                                <svg class="w-5 h-5 text-[var(--text-secondary)] transition-transform {{ $isExpanded ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            @if($isExpanded && $evaluation)
                                <div class="px-5 pb-8 pt-6 border-t border-[var(--border)]">
                                    <div class="pl-5 space-y-6">
                                        <p class="text-sm text-[var(--text-secondary)] leading-relaxed">
                                            {{ $evaluation->reasoning }}
                                        </p>
                                        <div class="flex flex-wrap gap-2 pt-2">
                                            @if($evaluation->primaryGoal)
                                                <span class="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                                                    {{ $evaluation->primaryGoal->normalized_title }}
                                                </span>
                                            @endif
                                            @if($evaluation->action_recommendation === 'delegate' && $evaluation->delegation_target_role)
                                                <span class="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                                                    Delegate to {{ $evaluation->delegation_target_role }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Low Impact -->
        @if($lowTasks->count() > 0)
            <div>
                <div class="text-xs text-[var(--text-secondary)] mb-4 uppercase tracking-wide">Low Impact</div>
                <div class="space-y-3">
                    @foreach($lowTasks as $todo)
                        @php
                            $evaluation = $todo->evaluation;
                            $isExpanded = in_array($todo->id, $expandedTasks);
                        @endphp
                        <div class="bg-[var(--bg-secondary)] rounded-xl border border-[var(--border)] overflow-hidden">
                            <button
                                wire:click="toggleTask('{{ $todo->id }}')"
                                class="w-full flex items-center gap-4 p-5 hover:bg-[var(--bg-hover)] transition-colors text-left"
                            >
                                <div class="w-1 h-12 rounded-full shrink-0" style="background-color: {{ $impactColors['low'] }};"></div>
                                <div class="flex-1">
                                    <div class="text-[var(--text-primary)] text-sm">{{ $todo->normalized_title }}</div>
                                </div>
                                <span class="px-3 py-1 rounded-lg text-xs border shrink-0" style="background-color: {{ $impactColors['low'] }}10; color: {{ $impactColors['low'] }}; border-color: {{ $impactColors['low'] }}30;">
                                    Low
                                </span>
                                <span class="px-3 py-1 rounded-lg text-xs bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border border-[var(--border)] shrink-0">
                                    Score: {{ $evaluation?->score ?? 0 }}
                                </span>
                                <svg class="w-5 h-5 text-[var(--text-secondary)] transition-transform {{ $isExpanded ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            @if($isExpanded && $evaluation)
                                <div class="px-5 pb-8 pt-6 border-t border-[var(--border)]">
                                    <div class="pl-5 space-y-6">
                                        <p class="text-sm text-[var(--text-secondary)] leading-relaxed">
                                            {{ $evaluation->reasoning }}
                                        </p>
                                        <div class="flex flex-wrap gap-2 pt-2">
                                            @if($evaluation->action_recommendation === 'drop')
                                                <span class="px-3 py-1.5 bg-[rgba(255,138,101,0.1)] text-[#FF8A65] text-xs rounded-lg border border-[rgba(255,138,101,0.3)]">
                                                    Consider dropping
                                                </span>
                                            @endif
                                            @if($evaluation->action_recommendation === 'delegate' && $evaluation->delegation_target_role)
                                                <span class="px-3 py-1.5 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                                                    Delegate to {{ $evaluation->delegation_target_role }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Empty State -->
        @if($sortedTodos->count() === 0)
            <div class="bg-[var(--bg-secondary)] rounded-2xl p-12 border border-[var(--border)] text-center">
                <p class="text-[var(--text-secondary)]">No tasks to display</p>
            </div>
        @endif
    </div>

    <!-- FOCUS SUGGESTIONS SECTION -->
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)] mb-12">
        <h2 class="text-[var(--text-primary)] mb-2">Focus Suggestions</h2>
        <p class="text-sm text-[var(--text-secondary)] mb-6">High-impact tasks suggested by AI to help you reach your goals:</p>
        
        @if($run->missingTodos->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($run->missingTodos as $missing)
                    <div class="bg-[var(--bg-tertiary)] rounded-xl p-4 border border-[var(--border)] hover:border-[rgba(233,75,140,0.3)] transition-colors">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="text-sm font-medium text-[var(--text-primary)]">{{ $missing->title }}</h3>
                            @if($missing->impact_score)
                                <span class="px-2 py-1 rounded-lg text-xs font-medium" style="background-color: {{ $missing->impact_score >= 70 ? '#4CAF50' : ($missing->impact_score >= 50 ? '#FFB74D' : '#FF8A65') }}10; color: {{ $missing->impact_score >= 70 ? '#4CAF50' : ($missing->impact_score >= 50 ? '#FFB74D' : '#FF8A65') }};">
                                    {{ $missing->impact_score }}
                                </span>
                            @endif
                        </div>
                        
                        @if($missing->description)
                            <p class="text-xs text-[var(--text-secondary)] mb-3 leading-relaxed">{{ $missing->description }}</p>
                        @endif

                        <div class="flex flex-wrap gap-2">
                            @if($missing->goal)
                                <span class="px-2 py-1 bg-[var(--accent-pink-light)] text-[var(--accent-pink)] text-xs rounded-lg border border-[rgba(233,75,140,0.3)]">
                                    {{ $missing->goal->title }}
                                </span>
                            @endif
                            @if($missing->category)
                                <span class="px-2 py-1 bg-[var(--bg-secondary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                                    {{ ucfirst($missing->category) }}
                                </span>
                            @endif
                            @if($missing->suggested_owner_role)
                                <span class="px-2 py-1 bg-[var(--bg-secondary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                                    {{ $missing->suggested_owner_role }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-[var(--bg-tertiary)] rounded-xl p-8 text-center border border-[var(--border)]">
                <svg class="w-12 h-12 mx-auto mb-3 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                </svg>
                <p class="text-sm text-[var(--text-secondary)]">No suggestions available yet. Run more analyses to get AI-powered task recommendations.</p>
            </div>
        @endif
    </div>

    <!-- HOW AXIA ANALYZED THIS -->
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 border border-[var(--border)] mb-12">
        <h3 class="text-[var(--text-primary)] mb-4">How was this analyzed?</h3>
        <p class="text-[var(--text-secondary)] mb-6">
            Axia uses a weighted formula that compares your task list against your stated goals, company context, 
            and timeframe. Tasks are scored based on their direct contribution to high-priority objectives, potential 
            revenue impact, and urgency within your current period.
        </p>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs text-[var(--text-secondary)]">Context used:</span>
            <span class="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                Company info
            </span>
            <span class="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                Goals ({{ $run->company?->goals->count() ?? 0 }})
            </span>
            <span class="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                Task list ({{ $sortedTodos->count() }})
            </span>
            @if($run->snapshotTopKpi)
                <span class="px-3 py-1 bg-[var(--bg-tertiary)] text-[var(--text-secondary)] text-xs rounded-lg border border-[var(--border)]">
                    Top KPI: {{ $run->snapshotTopKpi->name }}
                </span>
            @endif
        </div>
    </div>

    <!-- ACTIONS -->
    <div class="flex flex-col sm:flex-row gap-4">
        <button
            wire:click="exportCsv"
            class="flex-1 px-6 py-3 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] text-[var(--text-primary)] border border-[var(--border)] rounded-lg transition-colors text-sm flex items-center justify-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export as CSV
        </button>
        
        <a
            href="{{ route('home') }}"
            wire:navigate
            class="flex-1 px-6 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors text-sm text-center"
        >
            New Analysis
        </a>
    </div>

    <!-- Error Messages -->
    @if(session()->has('error'))
        <div class="mt-6 p-4 bg-[rgba(255,138,101,0.1)] border border-[rgba(255,138,101,0.3)] rounded-xl">
            <p class="text-sm text-[var(--accent-orange)]">{{ session('error') }}</p>
        </div>
    @endif
</div>
