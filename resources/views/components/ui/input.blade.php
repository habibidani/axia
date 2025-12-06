@props([
    'type' => 'text',
    'label' => null,
    'icon' => null, // slot name for icon
    'error' => null,
])

<div class="space-y-2">
    @if($label)
        <label class="block text-sm text-[var(--text-primary)]">{{ $label }}</label>
    @endif
    
    <div class="relative">
        @if($icon)
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-secondary)]">
                {{ $icon }}
            </div>
        @endif
        
        <input 
            type="{{ $type }}"
            {{ $attributes->merge([
                'class' => 'w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors' . ($icon ? ' pl-10' : '') . ($error ? ' border-red-500' : '')
            ]) }}
        />
    </div>
    
    @if($error)
        <p class="text-xs text-red-500">{{ $error }}</p>
    @endif
</div>

