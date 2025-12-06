<div class="max-w-3xl mx-auto px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-[var(--text-primary)] mb-2">Goals for this period</h1>
        <p>Define your key goals with clear priorities.</p>
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

    <!-- Manual Entry Form -->
    <form wire:submit.prevent="save">

        <!-- Goals List -->
        <div class="space-y-6">
            @foreach($goals as $goalIndex => $goal)
                <div class="bg-[var(--bg-secondary)] rounded-2xl border border-[var(--border)] p-6 space-y-4">
                    <!-- Goal Title with Delete -->
                    <div class="relative">
                        <input
                            type="text"
                            wire:model="goals.{{ $goalIndex }}.title"
                            placeholder="Describe your goal"
                            class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 pr-10 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)]"
                        />
                        @if(count($goals) > 1)
                            <button
                                type="button"
                                wire:click="removeGoal({{ $goalIndex }})"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-secondary)] hover:text-[var(--accent-orange)] transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    <!-- Description -->
                    <textarea
                        wire:model="goals.{{ $goalIndex }}.description"
                        placeholder="Additional details (optional)"
                        rows="2"
                        class="w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] resize-none text-sm"
                    ></textarea>

                    <!-- Priority Selection -->
                    <div>
                        <label class="block text-sm text-[var(--text-secondary)] mb-2">Priority</label>
                        <div class="flex gap-3">
                            @foreach(['high', 'medium', 'low'] as $priority)
                                @php
                                    $priorityColors = [
                                        'high' => ['bg' => 'rgba(76,175,80,0.1)', 'border' => 'rgba(76,175,80,0.5)', 'text' => '#4CAF50'],
                                        'medium' => ['bg' => 'rgba(255,183,77,0.1)', 'border' => 'rgba(255,183,77,0.5)', 'text' => '#FFB74D'],
                                        'low' => ['bg' => 'rgba(255,138,101,0.1)', 'border' => 'rgba(255,138,101,0.5)', 'text' => '#FF8A65'],
                                    ];
                                    $isSelected = ($goal['priority'] ?? 'medium') === $priority;
                                @endphp
                                <button
                                    type="button"
                                    wire:click="$set('goals.{{ $goalIndex }}.priority', '{{ $priority }}')"
                                    class="px-6 py-2 rounded-lg border transition-colors text-sm"
                                    style="{{ $isSelected ? "background-color: {$priorityColors[$priority]['bg']}; border-color: {$priorityColors[$priority]['border']}; color: {$priorityColors[$priority]['text']};" : '' }}"
                                    @if(!$isSelected)
                                        class="bg-[var(--bg-tertiary)] border-[var(--border)] text-[var(--text-secondary)] hover:border-[var(--text-secondary)]"
                                    @endif
                                >
                                    {{ ucfirst($priority) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- Add Goal Button -->
        <button
            type="button"
            wire:click="addGoal"
            class="w-full mt-6 py-4 bg-[var(--bg-secondary)] hover:bg-[var(--bg-hover)] border-2 border-dashed border-[var(--border)] hover:border-[rgba(233,75,140,0.3)] rounded-2xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors flex items-center justify-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add goal
        </button>

        <!-- Actions -->
        <div class="flex justify-end gap-3 mt-8">
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
                Continue to To-Dos
            </button>
        </div>
    </form>
</div>
