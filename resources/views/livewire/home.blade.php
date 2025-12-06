<div class="max-w-3xl mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-[var(--text-primary)] mb-2">Your To-Dos</h1>
        <p>Paste, type, or upload your current tasks.</p>
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

    <!-- Quick Stats (if company exists) -->
    @if($company)
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-[var(--bg-secondary)] rounded-xl p-4 border border-[var(--border)]">
                <div class="text-xs text-[var(--text-secondary)] uppercase tracking-wide mb-1">Company</div>
                <div class="text-sm text-[var(--text-primary)] font-medium truncate">{{ $company->name ?? 'Not set' }}</div>
            </div>
            <div class="bg-[var(--bg-secondary)] rounded-xl p-4 border border-[var(--border)]">
                <div class="text-xs text-[var(--text-secondary)] uppercase tracking-wide mb-1">Top Goal</div>
                <div class="text-sm text-[var(--text-primary)] font-medium truncate">{{ $topGoal?->title ?? 'No goals' }}</div>
            </div>
            <div class="bg-[var(--bg-secondary)] rounded-xl p-4 border border-[var(--border)]">
                <div class="text-xs text-[var(--text-secondary)] uppercase tracking-wide mb-1">Last Score</div>
                @if($lastRun)
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-[var(--text-primary)] font-medium">{{ $lastRun->overall_score }}/100</span>
                        <a href="{{ route('results.show', $lastRun) }}" wire:navigate class="text-xs text-[var(--accent-pink)] hover:underline">View →</a>
                    </div>
                @else
                    <div class="text-sm text-[var(--text-secondary)]">No analysis yet</div>
                @endif
            </div>
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-[var(--bg-secondary)] rounded-2xl p-8 space-y-8 border border-[var(--border)]">
        
        <!-- Bulk Input -->
        <div class="space-y-3">
            <label class="block text-sm text-[var(--text-primary)]">Paste To-Dos (one per line)</label>
            <textarea
                wire:model="todoText"
                placeholder="Write blog post&#10;Update landing page&#10;Review analytics&#10;Schedule team meeting"
                rows="8"
                class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] resize-none text-sm"
            ></textarea>
        </div>

        <div class="h-px bg-[var(--border)]"></div>

        <!-- File Upload -->
        <div class="flex items-center gap-4">
            <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] border border-[var(--border)] rounded-lg text-[var(--text-primary)] transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Upload CSV
                <input
                    type="file"
                    wire:model="csvFile"
                    accept=".csv,.txt"
                    class="hidden"
                />
            </label>
            
            @if($csvFile)
                <span class="text-sm text-[var(--accent-green)]">
                    ✓ {{ $csvFile->getClientOriginalName() }}
                </span>
                <button
                    type="button"
                    wire:click="uploadCsv"
                    class="px-4 py-2 bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] border border-[var(--border)] rounded-lg text-[var(--text-primary)] transition-colors text-sm"
                >
                    Analyze CSV
                </button>
            @endif
            
            <div wire:loading wire:target="csvFile" class="text-sm text-[var(--text-secondary)]">
                Uploading...
            </div>
        </div>

        <p class="text-xs text-[var(--text-secondary)]">
            CSV should have a "Task" column. Optional: "Owner", "Due Date" columns.
        </p>

        <!-- Analyze Button -->
        <div class="flex justify-center pt-6">
            <button
                wire:click="analyzeTodos"
                wire:loading.attr="disabled"
                wire:target="analyzeTodos"
                @disabled(empty($todoText ?? ''))
                class="px-12 py-3 bg-[#E94B8C] hover:bg-[#D43F7C] text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
                <span wire:loading.remove wire:target="analyzeTodos">Start Analysis</span>
                <span wire:loading wire:target="analyzeTodos" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Analyzing...
                </span>
            </button>
        </div>
    </div>

    <!-- Past Analyses (if any) -->
    @if($lastRun)
        <div class="mt-8">
            <h2 class="text-lg text-[var(--text-primary)] mb-4">Recent Analysis</h2>
            <a 
                href="{{ route('results.show', $lastRun) }}" 
                wire:navigate
                class="block bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)] hover:border-[rgba(233,75,140,0.3)] transition-colors"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-[var(--text-primary)] font-medium mb-1">
                            Focus Score: {{ $lastRun->overall_score }}/100
                        </div>
                        <div class="text-xs text-[var(--text-secondary)]">
                            {{ $lastRun->todos->count() }} tasks analyzed • {{ $lastRun->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- Score Circle -->
                        @php
                            $scoreColor = $lastRun->overall_score >= 70 ? '#4CAF50' : ($lastRun->overall_score >= 50 ? '#FFB74D' : '#FF8A65');
                        @endphp
                        <div 
                            class="w-12 h-12 rounded-full flex items-center justify-center"
                            style="border: 3px solid {{ $scoreColor }}30; background-color: {{ $scoreColor }}05;"
                        >
                            <span class="text-lg font-medium text-[var(--text-primary)]">{{ $lastRun->overall_score }}</span>
                        </div>
                        <svg class="w-5 h-5 text-[var(--text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    @endif

    <!-- Setup Prompts (if missing company or goals) -->
    @if(!$company || !$company->name)
        <div class="mt-8 bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-[rgba(233,75,140,0.1)] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[var(--accent-pink)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-[var(--text-primary)] mb-1">Set up your company first</h3>
                    <p class="text-xs text-[var(--text-secondary)] mb-3">Tell Axia about your business for better prioritization.</p>
                    <a 
                        href="{{ route('company.edit') }}" 
                        wire:navigate
                        class="inline-flex items-center gap-1 text-sm text-[var(--accent-pink)] hover:underline"
                    >
                        Set up company →
                    </a>
                </div>
            </div>
        </div>
    @elseif(!$topGoal)
        <div class="mt-8 bg-[var(--bg-secondary)] rounded-2xl p-6 border border-[var(--border)]">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-[rgba(233,75,140,0.1)] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[var(--accent-pink)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <circle cx="12" cy="12" r="6" />
                        <circle cx="12" cy="12" r="2" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-[var(--text-primary)] mb-1">Add your goals</h3>
                    <p class="text-xs text-[var(--text-secondary)] mb-3">Define what you're working towards for smarter analysis.</p>
                    <a 
                        href="{{ route('goals.edit') }}" 
                        wire:navigate
                        class="inline-flex items-center gap-1 text-sm text-[var(--accent-pink)] hover:underline"
                    >
                        Add goals →
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
