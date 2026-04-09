@php
    $isAdminSurface = request()->routeIs('admin.*') && auth()->user()?->is_admin;
    $navigationKey = $isAdminSurface ? 'admin' : 'member';
    $configuredItems = collect(config("mlm.navigation.{$navigationKey}", []));
    $items = $configuredItems
        ->filter(fn (array $item): bool => (bool) ($item['mobile_primary'] ?? false))
        ->take(5);

    if ($items->isEmpty()) {
        $items = $configuredItems->take(5);
    }
@endphp

<nav class="mobile-bottom-bar lg:hidden" aria-label="{{ __('Primary Mobile Navigation') }}">
    @foreach ($items as $item)
        @php
            $isCurrent = request()->routeIs($item['pattern']);
        @endphp

        <a
            href="{{ route($item['route']) }}"
            class="mobile-bottom-link {{ $isCurrent ? 'is-current' : '' }}"
            aria-current="{{ $isCurrent ? 'page' : 'false' }}"
            wire:navigate
        >
            <span class="mobile-bottom-icon">
                <flux:icon :name="$item['icon']" class="size-5" />
            </span>
            <span class="mobile-bottom-label">{{ __($item['short_label']) }}</span>
        </a>
    @endforeach
</nav>
