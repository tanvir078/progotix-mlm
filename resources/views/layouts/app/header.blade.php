<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    @php
        $brand = config('mlm.brand');
        $memberNavigation = config('mlm.navigation.member');
        $adminNavigation = config('mlm.navigation.admin');
    @endphp
    <body class="app-shell min-h-screen">
        <flux:header container class="app-topbar border-b">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <div class="ms-3 hidden xl:block">
                <p class="app-brand-badge">{{ $brand['tagline'] }}</p>
            </div>

            <flux:navbar class="-mb-px max-lg:hidden">
                @foreach ($memberNavigation as $item)
                    <flux:navbar.item :icon="$item['icon']" :href="route($item['route'])" :current="request()->routeIs($item['pattern'])" wire:navigate>
                        {{ __($item['short_label']) }}
                    </flux:navbar.item>
                @endforeach
                @if (auth()->user()->is_admin)
                    <flux:navbar.item icon="shield-check" :href="route('admin.dashboard')" :current="request()->routeIs('admin.*')" wire:navigate>
                        {{ __('Admin') }}
                    </flux:navbar.item>
                @endif
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <flux:tooltip :content="__('Referral Link')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="link"
                        :href="auth()->user()->referral_link"
                        :label="__('Referral Link')"
                    />
                </flux:tooltip>
            </flux:navbar>

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="app-sidebar lg:hidden border-e">
            <flux:sidebar.header>
                <div class="space-y-3">
                    <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                    <div class="rounded-2xl border border-white/10 bg-white/60 p-3 text-sm shadow-sm dark:bg-zinc-900/70">
                        <p class="app-brand-badge">{{ $brand['name'] }}</p>
                        <p class="mt-3 font-semibold text-zinc-900 dark:text-white">{{ $brand['tagline'] }}</p>
                    </div>
                </div>
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    @foreach ($memberNavigation as $item)
                        <flux:sidebar.item :icon="$item['icon']" :href="route($item['route'])" :current="request()->routeIs($item['pattern'])" wire:navigate>
                            {{ __($item['label']) }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>

                @if (auth()->user()->is_admin)
                    <flux:sidebar.group :heading="__('Admin')">
                        @foreach ($adminNavigation as $item)
                            <flux:sidebar.item :icon="$item['icon']" :href="route($item['route'])" :current="request()->routeIs($item['pattern'])" wire:navigate>
                                {{ __($item['label']) }}
                            </flux:sidebar.item>
                        @endforeach
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="link" :href="auth()->user()->referral_link">
                    {{ __('My Referral Link') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        <x-mobile-bottom-nav />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
