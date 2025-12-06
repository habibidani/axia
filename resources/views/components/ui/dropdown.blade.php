@props([
    'align' => 'right', // left, right
    'width' => '240px',
])

@php
    $alignmentClasses = [
        'left' => 'left-0',
        'right' => 'right-0',
    ];
@endphp

<div x-data="{ open: false }" class="relative" @click.away="open = false">
    <!-- Trigger -->
    <div @click="open = !open" class="cursor-pointer">
        {{ $trigger }}
    </div>
    
    <!-- Dropdown Content -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        class="absolute {{ $alignmentClasses[$align] ?? 'right-0' }} mt-2 bg-[var(--bg-secondary)] rounded-xl shadow-lg border border-[var(--border)] overflow-hidden z-50"
        style="width: {{ $width }};"
    >
        {{ $slot }}
    </div>
</div>

