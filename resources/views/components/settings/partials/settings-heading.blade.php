@props([
    'heading',
    'description',
])

<div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $heading }}</h3>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $description }}</p>
</div>
