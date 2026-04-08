<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
                <flux:navbar.item icon="user-group" :href="route('mlm.network')" :current="request()->routeIs('mlm.network')" wire:navigate>
                    {{ __('Network') }}
                </flux:navbar.item>
                <flux:navbar.item icon="cube" :href="route('mlm.plans.index')" :current="request()->routeIs('mlm.plans.*')" wire:navigate>
                    {{ __('Packages') }}
                </flux:navbar.item>
                <flux:navbar.item icon="banknotes" :href="route('mlm.earnings')" :current="request()->routeIs('mlm.earnings')" wire:navigate>
                    {{ __('Earnings') }}
                </flux:navbar.item>
                <flux:navbar.item icon="squares-2x2" :href="route('mlm.binary-tree')" :current="request()->routeIs('mlm.binary-tree')" wire:navigate>
                    {{ __('Tree') }}
                </flux:navbar.item>
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
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard')  }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-group" :href="route('mlm.network')" :current="request()->routeIs('mlm.network')" wire:navigate>
                        {{ __('Network') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cube" :href="route('mlm.plans.index')" :current="request()->routeIs('mlm.plans.*')" wire:navigate>
                        {{ __('Packages') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('mlm.earnings')" :current="request()->routeIs('mlm.earnings')" wire:navigate>
                        {{ __('Earnings') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="squares-2x2" :href="route('mlm.binary-tree')" :current="request()->routeIs('mlm.binary-tree')" wire:navigate>
                        {{ __('Tree') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrows-right-left" :href="route('mlm.withdrawals.index')" :current="request()->routeIs('mlm.withdrawals.*')" wire:navigate>
                        {{ __('Withdrawals') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :href="route('mlm.invoices')" :current="request()->routeIs('mlm.invoices')" wire:navigate>
                        {{ __('Invoices') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if (auth()->user()->is_admin)
                    <flux:sidebar.group :heading="__('Admin')">
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
        </flux:sidebar>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
