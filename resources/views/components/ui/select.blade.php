@props([
    'label' => null,
    'error' => null,
    'options' => [],
    'placeholder' => 'Select an option',
])

<div class="space-y-2">
    @if($label)
        <label class="block text-sm text-[var(--text-primary)]">{{ $label }}</label>
    @endif
    
    <div class="relative">
        <select {{ $attributes->merge([
            'class' => 'w-full bg-[var(--bg-tertiary)] border border-[var(--border)] rounded-lg px-4 py-3 text-sm text-[var(--text-primary)] focus:outline-none focus:border-[rgba(233,75,140,0.5)] transition-colors appearance-none cursor-pointer' . ($error ? ' border-red-500' : '')
        ]) }}>
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            
            @if(count($options) > 0)
                @foreach($options as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            @else
                {{ $slot }}
            @endif
        </select>
        
        <!-- Dropdown Arrow -->
        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[var(--text-secondary)]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>
    
    @if($error)
        <p class="text-xs text-red-500">{{ $error }}</p>
    @endif
</div>

