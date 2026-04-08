<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-group" :href="route('mlm.network')" :current="request()->routeIs('mlm.network')" wire:navigate>
                        {{ __('Referral Network') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cube" :href="route('mlm.plans.index')" :current="request()->routeIs('mlm.plans.*')" wire:navigate>
                        {{ __('Packages') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('mlm.earnings')" :current="request()->routeIs('mlm.earnings')" wire:navigate>
                        {{ __('Earnings') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="squares-2x2" :href="route('mlm.binary-tree')" :current="request()->routeIs('mlm.binary-tree')" wire:navigate>
                        {{ __('Binary Tree') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrows-right-left" :href="route('mlm.withdrawals.index')" :current="request()->routeIs('mlm.withdrawals.*')" wire:navigate>
                        {{ __('Withdrawals') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('mlm.invoices')" :current="request()->routeIs('mlm.invoices')" wire:navigate>
                        {{ __('Invoices') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if (auth()->user()->is_admin)
                    <flux:sidebar.group :heading="__('Admin')" class="grid">
                        <flux:sidebar.item icon="shield-check" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                            {{ __('Admin Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('admin.members')" :current="request()->routeIs('admin.members')" wire:navigate>
                            {{ __('Members') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="cube" :href="route('admin.plans')" :current="request()->routeIs('admin.plans*')" wire:navigate>
                            {{ __('Plan CRUD') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="squares-plus" :href="route('admin.binary-tree')" :current="request()->routeIs('admin.binary-tree')" wire:navigate>
                            {{ __('Tree Manager') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="credit-card" :href="route('admin.withdrawals')" :current="request()->routeIs('admin.withdrawals*')" wire:navigate>
                            {{ __('Withdrawal Queue') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-duplicate" :href="route('admin.invoices')" :current="request()->routeIs('admin.invoices')" wire:navigate>
                            {{ __('Invoices') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="presentation-chart-line" :href="route('admin.reports')" :current="request()->routeIs('admin.reports')" wire:navigate>
                            {{ __('Reports') }}
                        </flux:sidebar.item>
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
        <flux:header class="lg:hidden">
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

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
