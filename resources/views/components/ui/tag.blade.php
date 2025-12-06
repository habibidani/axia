@props([
    'variant' => 'default', // default, outline
])

@php
    $variants = [
        'default' => 'bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border-[var(--border)]',
        'outline' => 'bg-transparent text-[var(--text-secondary)] border-[var(--border)]',
    ];
    
    $classes = 'inline-flex items-center px-3 py-1.5 rounded-lg text-xs border ' . ($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>

