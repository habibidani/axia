@props([
    'label' => null,
    'error' => null,
    'rows' => 4,
])

<div class="space-y-2">
    @if($label)
        <label class="block text-sm text-[var(--text-primary)]">{{ $label }}</label>
    @endif
    
    <textarea 
        rows="{{ $rows }}"
        {{ $attributes->merge([
            'class' => 'w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] placeholder-[var(--text-secondary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors resize-none' . ($error ? ' border-red-500' : '')
        ]) }}
    >{{ $slot }}</textarea>
    
    @if($error)
        <p class="text-xs text-red-500">{{ $error }}</p>
    @endif
</div>

