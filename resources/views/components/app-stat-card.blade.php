@props([
    'label',
    'value',
    'meta' => null,
    'icon' => null,
    'tone' => 'default',
])

@php
    $toneClasses = [
        'default' => 'app-stat-card',
        'brand' => 'app-stat-card app-stat-card-brand',
        'accent' => 'app-stat-card app-stat-card-accent',
    ];
@endphp

<section {{ $attributes->class([$toneClasses[$tone] ?? $toneClasses['default']]) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $label }}</p>
            <p class="mt-3 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white sm:text-3xl">{{ $value }}</p>

            @if ($meta)
                <p class="mt-2 text-xs uppercase tracking-[0.24em] text-zinc-400 dark:text-zinc-500">{{ $meta }}</p>
            @endif
        </div>

        @if ($icon)
            <span class="app-stat-icon">
                <flux:icon :name="$icon" class="size-5" />
            </span>
        @endif
    </div>

    @if (trim((string) $slot) !== '')
        <div class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
            {{ $slot }}
        </div>
    @endif
</section>
