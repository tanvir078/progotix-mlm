@props([
    'spacing' => 'default',
])

@php
    $spacingClasses = [
        'tight' => 'gap-5 lg:gap-6',
        'default' => 'gap-6 lg:gap-7',
        'loose' => 'gap-8 lg:gap-10',
    ];
@endphp

<div {{ $attributes->class(['app-page', $spacingClasses[$spacing] ?? $spacingClasses['default']]) }}>
    {{ $slot }}
</div>
