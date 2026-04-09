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
    <body class="app-shell min-h-screen overflow-x-hidden">
        <flux:sidebar sticky collapsible="mobile" class="app-sidebar border-e">
            <flux:sidebar.header>
                <div class="space-y-3">
                    <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                    <div class="rounded-2xl border border-white/10 bg-white/60 p-3 text-sm shadow-sm dark:bg-zinc-900/70">
                        <p class="app-brand-badge">{{ $brand['name'] }}</p>
                        <p class="mt-3 font-semibold text-zinc-900 dark:text-white">{{ $brand['tagline'] }}</p>
                        <p class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">{{ $brand['description'] }}</p>
                    </div>
                </div>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    @foreach ($memberNavigation as $item)
                        <flux:sidebar.item :icon="$item['icon']" :href="route($item['route'])" :current="request()->routeIs($item['pattern'])" wire:navigate>
                            {{ __($item['label']) }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>

                @if (auth()->user()->is_admin)
                    <flux:sidebar.group :heading="__('Admin')" class="grid">
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

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="app-topbar lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        @if (auth()->user()->is_admin)
                            <flux:menu.item :href="route('admin.dashboard')" icon="shield-check" wire:navigate>
                                {{ __('Admin Panel') }}
                            </flux:menu.item>
                        @endif
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

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
