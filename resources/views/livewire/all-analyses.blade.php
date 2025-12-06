<div class="max-w-4xl mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-[var(--text-primary)] mb-2">All Analyses</h1>
        <p class="text-[var(--text-secondary)]">View your past focus score analyses and insights.</p>
    </div>

    @if($runs->count() > 0)
        <div class="space-y-4">
            @foreach($runs as $run)
                @php
                    $score = $run->overall_score ?? 0;
                    $scoreColor = $score >= 70 ? '#4CAF50' : ($score >= 50 ? '#FFB74D' : '#FF8A65');
                    $todoCount = $run->todos->count();
                @endphp
                
                <a 
                    href="{{ route('results.show', $run) }}"
                    wire:navigate
                    class="block bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] p-6 hover:border-[rgba(233,75,140,0.3)] transition-colors group"
                >
                    <div class="flex items-center justify-between">
                        <!-- Left: Date & Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-sm text-[var(--text-secondary)]">
                                    {{ $run->created_at->format('M d, Y') }}
                                </span>
                                <span class="text-xs text-[var(--text-secondary)]">•</span>
                                <span class="text-sm text-[var(--text-secondary)]">
                                    {{ $run->created_at->format('H:i') }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-[var(--text-primary)] font-medium">
                                    {{ $todoCount }} {{ $todoCount === 1 ? 'task' : 'tasks' }} analyzed
                                </span>
                                @if($run->company?->name)
                                    <span class="px-2 py-1 text-xs rounded-lg bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border border-[var(--border)]">
                                        {{ $run->company->name }}
                                    </span>
                                @endif
                            </div>
                            @if($run->summary_text)
                                <p class="text-sm text-[var(--text-secondary)] mt-2 line-clamp-1">
                                    {{ Str::limit($run->summary_text, 100) }}
                                </p>
                            @endif
                        </div>

                        <!-- Right: Score -->
                        <div class="flex items-center gap-4">
                            <div 
                                class="w-16 h-16 rounded-full flex items-center justify-center shrink-0"
                                style="border: 3px solid {{ $scoreColor }}30; background-color: {{ $scoreColor }}10;"
                            >
                                <span class="text-xl font-semibold" style="color: {{ $scoreColor }};">
                                    {{ $score }}
                                </span>
                            </div>
                            <svg class="w-5 h-5 text-[var(--text-secondary)] group-hover:text-[var(--accent-pink)] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-[var(--bg-tertiary)] flex items-center justify-center">
                <svg class="w-8 h-8 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-[var(--text-primary)] mb-2">No analyses yet</h3>
            <p class="text-[var(--text-secondary)] mb-6">Run your first analysis to see your focus score and task prioritization.</p>
            <a 
                href="{{ route('home') }}"
                wire:navigate
                class="inline-flex items-center gap-2 px-6 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Start Analysis
            </a>
        </div>
    @endif
</div>

