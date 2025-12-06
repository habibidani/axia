@props([
    'score' => 0,
    'size' => 'default', // sm, default, lg
])

@php
    // Determine color based on score
    if ($score >= 70) {
        $color = '#4CAF50'; // Green
    } elseif ($score >= 50) {
        $color = '#FFB74D'; // Yellow
    } else {
        $color = '#FF8A65'; // Orange
    }
    
    $sizes = [
        'sm' => [
            'container' => 'w-24 h-24',
            'border' => '4px',
            'score' => 'text-3xl',
            'max' => 'text-xs',
        ],
        'default' => [
            'container' => 'w-40 h-40',
            'border' => '6px',
            'score' => 'text-5xl',
            'max' => 'text-xs',
        ],
        'lg' => [
            'container' => 'w-48 h-48',
            'border' => '8px',
            'score' => 'text-6xl',
            'max' => 'text-sm',
        ],
    ];
    
    $sizeConfig = $sizes[$size] ?? $sizes['default'];
@endphp

<div class="flex flex-col items-center justify-center">
    <div 
        class="{{ $sizeConfig['container'] }} rounded-full flex items-center justify-center mb-4"
        style="
            border: {{ $sizeConfig['border'] }} solid {{ $color }}30;
            background-color: {{ $color }}05;
        "
    >
        <div class="text-center">
            <div class="{{ $sizeConfig['score'] }} text-[var(--text-primary)] mb-1 font-medium">{{ $score }}</div>
            <div class="{{ $sizeConfig['max'] }} text-[var(--text-secondary)]">/100</div>
        </div>
    </div>
    <div class="text-sm text-[var(--text-secondary)]">Focus Score</div>
</div>

