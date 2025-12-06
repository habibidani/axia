@props([
    'variant' => 'primary', // primary, secondary, ghost, outline
    'size' => 'default', // sm, default, lg, icon
    'href' => null,
    'type' => 'button',
    'disabled' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variants = [
        'primary' => 'bg-[var(--accent-pink)] hover:bg-[var(--accent-pink-hover)] text-white',
        'secondary' => 'bg-[var(--bg-tertiary)] hover:bg-[var(--bg-hover)] text-[var(--text-primary)] border border-[var(--border)]',
        'ghost' => 'bg-transparent hover:bg-[var(--bg-tertiary)] text-[var(--text-secondary)] hover:text-[var(--text-primary)]',
        'outline' => 'bg-transparent border border-[var(--border)] text-[var(--text-primary)] hover:bg-[var(--bg-tertiary)]',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
    ];
    
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs rounded-md',
        'default' => 'px-6 py-3 text-sm rounded-lg',
        'lg' => 'px-8 py-4 text-base rounded-xl',
        'icon' => 'w-9 h-9 rounded-lg',
    ];
    
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['default']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if($disabled) aria-disabled="true" @endif>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @disabled($disabled)>
        {{ $slot }}
    </button>
@endif

