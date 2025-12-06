@props([
    'text' => 'Loading...',
])

<div 
    {{ $attributes->merge(['class' => 'fixed inset-0 bg-[var(--bg-primary)]/80 backdrop-blur-sm flex items-center justify-center z-50']) }}
>
    <div class="text-center">
        <!-- Spinner -->
        <div class="w-12 h-12 mx-auto mb-4">
            <svg class="animate-spin w-full h-full text-[var(--accent-pink)]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
        <!-- Text -->
        <p class="text-sm text-[var(--text-secondary)]">{{ $text }}</p>
    </div>
</div>

