@props([
    'title',
    'description' => null,
    'eyebrow' => null,
])

<div {{ $attributes->class(['app-section-heading']) }}>
    <div class="space-y-2">
        @if ($eyebrow)
            <p class="app-kicker">{{ $eyebrow }}</p>
        @endif

        <div class="space-y-1">
            <h2 class="text-xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $title }}</h2>

            @if ($description)
                <p class="max-w-2xl text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
            @endif
        </div>
    </div>

    @isset($actions)
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
