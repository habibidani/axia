@props([
    'padding' => 'default', // none, sm, default, lg
    'rounded' => '2xl',
])

@php
    $paddings = [
        'none' => '',
        'sm' => 'p-4',
        'default' => 'p-6',
        'lg' => 'p-8',
    ];
    
    $paddingClass = $paddings[$padding] ?? $paddings['default'];
@endphp

<div {{ $attributes->merge(['class' => "bg-[var(--bg-secondary)] border border-[var(--border)] rounded-{$rounded} {$paddingClass}"]) }}>
    {{ $slot }}
</div>

