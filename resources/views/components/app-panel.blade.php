@props([
    'variant' => 'default',
    'padding' => 'default',
])

@php
    $variantClasses = [
        'default' => 'app-panel',
        'soft' => 'app-panel app-panel-soft',
        'contrast' => 'app-panel app-panel-contrast',
        'hero' => 'app-panel app-panel-hero',
    ];

    $paddingClasses = [
        'compact' => 'p-4 sm:p-5',
        'default' => 'p-5 sm:p-6',
        'relaxed' => 'p-6 sm:p-7',
        'hero' => 'p-5 sm:p-6 lg:p-8',
    ];
@endphp

<section {{ $attributes->class([
    $variantClasses[$variant] ?? $variantClasses['default'],
    $paddingClasses[$padding] ?? $paddingClasses['default'],
]) }}>
    {{ $slot }}
</section>
