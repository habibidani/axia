@props([
    'title' => '',
    'subtitle' => '',
])

<div class="px-4 py-3 border-b border-[var(--border)] bg-[var(--bg-tertiary)]">
    @if($title)
        <p class="text-sm font-semibold text-[var(--text-primary)]">{{ $title }}</p>
    @endif
    @if($subtitle)
        <p class="text-xs text-[var(--text-secondary)] mt-0.5">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
</div>

