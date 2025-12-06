@props([
    'href' => null,
    'icon' => null,
])

@php
    $baseClasses = 'flex items-center gap-3 px-4 py-2.5 text-sm text-[var(--text-primary)] hover:bg-[var(--bg-hover)] transition-colors w-full text-left';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if($icon)
            <span class="text-[var(--text-secondary)] shrink-0">{{ $icon }}</span>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if($icon)
            <span class="text-[var(--text-secondary)] shrink-0">{{ $icon }}</span>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif

