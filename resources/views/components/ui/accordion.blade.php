@props([
    'title' => '',
    'impact' => null, // high, mid, low
    'score' => null,
    'expanded' => false,
])

@php
    $impactColors = [
        'high' => '#4CAF50',
        'mid' => '#FFB74D',
        'low' => '#FF8A65',
    ];
    $impactColor = $impactColors[$impact] ?? '#6B7280';
    
    $impactLabels = [
        'high' => 'High',
        'mid' => 'Mid',
        'low' => 'Low',
    ];
    $impactLabel = $impactLabels[$impact] ?? null;
@endphp

<div 
    x-data="{ open: {{ $expanded ? 'true' : 'false' }} }" 
    class="bg-[var(--bg-secondary)] rounded-xl border border-[var(--border)] overflow-hidden"
>
    <!-- Accordion Header -->
    <button 
        @click="open = !open"
        class="w-full flex items-center gap-4 p-5 hover:bg-[var(--bg-hover)] transition-colors text-left"
    >
        <!-- Impact Bar -->
        @if($impact)
            <div 
                class="w-1 h-12 rounded-full shrink-0"
                style="background-color: {{ $impactColor }};"
            ></div>
        @endif
        
        <!-- Title -->
        <div class="flex-1">
            <div class="text-[var(--text-primary)] text-sm">{{ $title }}</div>
        </div>
        
        <!-- Impact Badge -->
        @if($impactLabel)
            <span 
                class="px-3 py-1 rounded-lg text-xs border shrink-0"
                style="
                    background-color: {{ $impactColor }}10;
                    color: {{ $impactColor }};
                    border-color: {{ $impactColor }}30;
                "
            >
                {{ $impactLabel }}
            </span>
        @endif
        
        <!-- Score Badge -->
        @if($score !== null)
            <span class="px-3 py-1 rounded-lg text-xs bg-[var(--bg-tertiary)] text-[var(--text-secondary)] border border-[var(--border)] shrink-0">
                Score: {{ $score }}
            </span>
        @endif
        
        <!-- Chevron -->
        <svg 
            class="w-5 h-5 text-[var(--text-secondary)] transition-transform duration-200 shrink-0"
            :class="{ 'rotate-180': open }"
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    
    <!-- Accordion Content -->
    <div 
        x-show="open" 
        x-collapse 
        x-cloak
    >
        <div class="px-5 pb-8 pt-6 border-t border-[var(--border)]">
            <div class="pl-5 space-y-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

