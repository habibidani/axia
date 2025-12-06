@props([
    'variant' => 'default', // default, high, mid, low, success, warning, danger
    'size' => 'default', // sm, default
])

@php
    $variants = [
        'default' => 'bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border-[var(--border)]',
        'high' => 'bg-[rgba(76,175,80,0.1)] text-[#4CAF50] border-[rgba(76,175,80,0.3)]',
        'mid' => 'bg-[rgba(255,183,77,0.1)] text-[#FFB74D] border-[rgba(255,183,77,0.3)]',
        'low' => 'bg-[rgba(255,138,101,0.1)] text-[#FF8A65] border-[rgba(255,138,101,0.3)]',
        'success' => 'bg-green-500/10 text-green-500 border-green-500/30',
        'warning' => 'bg-yellow-500/10 text-yellow-500 border-yellow-500/30',
        'danger' => 'bg-red-500/10 text-red-500 border-red-500/30',
        'pink' => 'bg-[var(--accent-pink-light)] text-[var(--accent-pink)] border-[rgba(233,75,140,0.3)]',
    ];
    
    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'default' => 'px-3 py-1 text-xs',
    ];
    
    $classes = 'inline-flex items-center rounded-lg border font-medium ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>

