<x-layouts::app :title="__('MLM Dashboard')">
    @php
        $headlineStats = [
            [
                'label' => 'Wallet Balance',
                'value' => '৳'.number_format((float) $stats['wallet_balance'], 2),
                'meta' => 'Available now',
                'icon' => 'wallet',
                'tone' => 'brand',
            ],
            [
                'label' => 'This Month',
                'value' => '৳'.number_format((float) $stats['monthly_earnings'], 2),
                'meta' => 'Posted earnings',
                'icon' => 'banknotes',
                'tone' => 'accent',
            ],
            [
                'label' => 'Direct Referrals',
                'value' => number_format((int) $stats['direct_referrals']),
                'meta' => 'Sponsor line',
                'icon' => 'user-group',
            ],
            [
                'label' => 'Team Size',
                'value' => number_format((int) $stats['team_size']),
                'meta' => 'Total network',
                'icon' => 'users',
            ],
            [
                'label' => 'Binary Team',
                'value' => number_format((int) $stats['binary_team_size']),
                'meta' => 'Placement tree',
                'icon' => 'squares-2x2',
            ],
            [
                'label' => 'Retail Sales',
                'value' => '$'.number_format((float) $stats['retail_sales'], 2),
                'meta' => 'Paid orders',
                'icon' => 'shopping-bag',
            ],
            [
                'label' => 'Direct Bonus Total',
                'value' => '৳'.number_format((float) $stats['direct_bonus_total'], 2),
                'meta' => 'Referral earnings',
                'icon' => 'arrow-trending-up',
            ],
            [
                'label' => 'Pending Withdrawals',
                'value' => '৳'.number_format((float) $stats['pending_withdrawals'], 2),
                'meta' => 'Awaiting payout',
                'icon' => 'arrows-right-left',
            ],
        ];
    @endphp

    <x-app-page spacing="loose">
        <x-app-panel variant="hero" padding="hero">
            <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr] xl:items-end">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <p class="app-kicker">ProgotiX Member Workspace</p>
                        <div class="space-y-4">
                            <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-white sm:text-4xl xl:text-[2.8rem]">
                                Mobile-friendly command center for network growth, retail orders, and wallet operations.
                            </h1>
                            <p class="max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
                                আপনার sponsor chain, package status, commission pulse, shop activity, আর payout readiness এক জায়গা থেকে monitor করার জন্য dashboard-টা clean structure-এ সাজানো হয়েছে।
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @foreach ($primaryWorkspaceModules as $module)
                            <a href="{{ $module['route'] }}" class="app-pill {{ $loop->first ? 'app-pill-secondary' : 'app-pill-primary' }}" wire:navigate>
                                <flux:icon :name="$module['icon']" class="mr-2 size-4" />
                                {{ $module['short_label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-4 sm:p-5 backdrop-blur">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/60">Referral Link</p>
                        <p class="mt-3 break-all text-sm leading-6 text-white">{{ $user->referral_link }}</p>
                        <p class="mt-3 text-xs leading-5 text-white/68">এই link share করলে direct signup গুলো আপনার sponsor line-এ আসবে।</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-2">
                        <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur">
                            <p class="text-xs uppercase tracking-[0.28em] text-white/58">Active Package</p>
                            <p class="mt-2 text-lg font-semibold text-white">{{ $activeSubscription?->plan?->name ?? 'Not active' }}</p>
                            <p class="mt-2 text-sm text-white/72">
                                {{ $activeSubscription ? '৳'.number_format((float) $activeSubscription->amount, 2) : 'Choose a package to start' }}
                            </p>
                        </div>

                        <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur">
                            <p class="text-xs uppercase tracking-[0.28em] text-white/58">Sponsor</p>
                            <p class="mt-2 text-lg font-semibold text-white">{{ $user->referrer?->name ?? 'Direct signup' }}</p>
                            <p class="mt-2 text-sm text-white/72">
                                {{ $user->referrer ? '@'.$user->referrer->username : 'No parent sponsor' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </x-app-panel>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($headlineStats as $card)
                <x-app-stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :meta="$card['meta']"
                    :icon="$card['icon']"
                    :tone="$card['tone'] ?? 'default'"
                />
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <x-app-panel variant="soft">
                <x-app-section-heading
                    title="Main Workspace"
                    description="প্রতিটা module-এর business role, daily workflow, আর next action structure একসাথে দেখানোর জন্য এই navigation map রাখা হয়েছে।"
                    eyebrow="Operations"
                >
                    <x-slot:actions>
                        <span class="app-brand-badge">Module Map</span>
                    </x-slot:actions>
                </x-app-section-heading>

                <div class="app-module-grid mt-6">
                    @foreach ($workspaceModules as $module)
                        <a href="{{ $module['route'] }}" class="app-module-card group" wire:navigate>
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-3">
                                    <p class="app-kicker">Module</p>
                                    <div class="space-y-2">
                                        <h3 class="text-xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $module['label'] }}</h3>
                                        <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $module['description'] }}</p>
                                    </div>
                                </div>

                                <span class="inline-flex size-11 items-center justify-center rounded-2xl border border-teal-200/70 bg-teal-50 text-teal-700 dark:border-teal-900 dark:bg-teal-950/30 dark:text-teal-200">
                                    <flux:icon :name="$module['icon']" class="size-5" />
                                </span>
                            </div>

                            <p class="mt-5 text-sm font-medium text-zinc-700 transition group-hover:text-teal-700 dark:text-zinc-200 dark:group-hover:text-teal-300">
                                Open module
                            </p>
                        </a>
                    @endforeach
                </div>
            </x-app-panel>

            <div class="grid gap-6">
                <x-app-panel>
                    <x-app-section-heading
                        title="Recommended MLM Direction"
                        description="Current strategic principles that shape safer growth, cleaner reporting, and better compensation control."
                        eyebrow="Strategy"
                    />

                    <p class="mt-5 text-base leading-7 text-zinc-700 dark:text-zinc-300">{{ $strategy['recommended_model'] }}</p>

                    <div class="mt-5 app-list-stack">
                        @foreach ($strategy['principles'] as $principle)
                            <div class="app-list-row">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 inline-flex size-6 items-center justify-center rounded-full bg-teal-500/12 text-teal-700 dark:text-teal-300">
                                        <flux:icon name="check" class="size-4" />
                                    </span>
                                    <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $principle }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-app-panel>

                <x-app-panel variant="contrast">
                    <x-app-section-heading
                        title="Commerce Preview"
                        description="Retail-first product line preview so members understand which offers drive volume."
                        eyebrow="Retail"
                    >
                        <x-slot:actions>
                            <a href="{{ route('mlm.shop') }}" class="app-inline-link" wire:navigate>Open shop</a>
                        </x-slot:actions>
                    </x-app-section-heading>

                    <div class="mt-5 app-list-stack">
                        @forelse ($featuredProducts as $product)
                            <div class="app-list-row">
                                <div class="space-y-2">
                                    <p class="font-semibold text-zinc-950 dark:text-white">{{ $product->name }}</p>
                                    <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $product->description }}</p>
                                </div>

                                <div class="text-left sm:text-right">
                                    <p class="text-lg font-semibold text-zinc-950 dark:text-white">${{ number_format((float) $product->price, 2) }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.24em] text-zinc-400">
                                        {{ (int) ((float) $product->retail_commission_rate * 100) }}% retail
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="app-empty-state">
                                এখনো কোনো active product নেই। Product catalog যোগ করলে commerce dashboard এখানেই preview দেখাবে।
                            </div>
                        @endforelse
                    </div>
                </x-app-panel>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <x-app-panel>
                <x-app-section-heading
                    title="Direct Network"
                    description="আপনার নিচে সরাসরি যোগ হওয়া সদস্যদের short operational view."
                    eyebrow="Growth"
                >
                    <x-slot:actions>
                        <a href="{{ route('mlm.network') }}" class="app-inline-link" wire:navigate>View full network</a>
                    </x-slot:actions>
                </x-app-section-heading>

                <div class="mt-5 app-list-stack">
                    @forelse ($directReferrals as $member)
                        <div class="app-list-row">
                            <div class="space-y-1">
                                <p class="font-semibold text-zinc-950 dark:text-white">{{ $member->name }}</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ '@'.$member->username }} • {{ $member->member_code }}</p>
                            </div>

                            <div class="grid gap-4 text-sm text-zinc-600 dark:text-zinc-300 sm:grid-cols-2">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.24em] text-zinc-400">Downline</p>
                                    <p class="mt-1 font-semibold">{{ number_format((int) $member->referrals_count) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.24em] text-zinc-400">Wallet</p>
                                    <p class="mt-1 font-semibold">৳{{ number_format((float) $member->balance, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="app-empty-state">
                            এখনো কোনো direct referral নেই। Referral link share করলেই নতুন member এখানে দেখা যাবে।
                        </div>
                    @endforelse
                </div>
            </x-app-panel>

            <div class="grid gap-6">
                <x-app-panel>
                    <x-app-section-heading
                        title="Current Package"
                        description="Active qualification and package readiness snapshot."
                        eyebrow="Qualification"
                    />

                    @if ($activeSubscription)
                        <div class="mt-5 rounded-[1.5rem] border border-zinc-200/80 bg-zinc-50/90 p-5 dark:border-zinc-700 dark:bg-zinc-800/60">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $activeSubscription->plan->name }}</p>
                            <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">৳{{ number_format((float) $activeSubscription->amount, 2) }}</p>
                            <div class="mt-4 flex flex-wrap gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                                <span>Activated {{ $activeSubscription->started_at?->diffForHumans() }}</span>
                                <span>Rank {{ $user->currentRank?->name ?? 'Starter' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="app-empty-state mt-5">
                            এখনো কোনো package activate করা হয়নি। Packages module থেকে activation শুরু করলে qualification flow চালু হবে।
                        </div>
                    @endif
                </x-app-panel>

                <x-app-panel>
                    <x-app-section-heading
                        title="Available Packages"
                        description="দ্রুত activation-এর জন্য short package preview."
                        eyebrow="Catalog"
                    >
                        <x-slot:actions>
                            <a href="{{ route('mlm.plans.index') }}" class="app-inline-link" wire:navigate>All packages</a>
                        </x-slot:actions>
                    </x-app-section-heading>

                    <div class="mt-5 app-list-stack">
                        @forelse ($plans->take(3) as $plan)
                            <div class="app-list-row">
                                <div class="space-y-2">
                                    <p class="font-semibold text-zinc-950 dark:text-white">{{ $plan->name }}</p>
                                    <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $plan->description }}</p>
                                </div>

                                <div class="text-left sm:text-right">
                                    <p class="text-lg font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $plan->price, 2) }}</p>
                                    <p class="mt-1 text-sm text-emerald-600 dark:text-emerald-400">
                                        Direct bonus: ৳{{ number_format((float) $plan->direct_bonus, 2) }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="app-empty-state">
                                কোনো active plan এখন নেই। Admin panel থেকে plan management set up করুন।
                            </div>
                        @endforelse
                    </div>
                </x-app-panel>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <x-app-panel>
                <x-app-section-heading
                    title="Recent Earnings Activity"
                    description="সর্বশেষ bonus এবং wallet movement trace করার short ledger."
                    eyebrow="Ledger"
                >
                    <x-slot:actions>
                        <a href="{{ route('mlm.earnings') }}" class="app-inline-link" wire:navigate>Open ledger</a>
                    </x-slot:actions>
                </x-app-section-heading>

                <div class="mt-5 space-y-3 md:hidden">
                    @forelse ($recentTransactions as $transaction)
                        <div class="app-list-row">
                            <div class="space-y-1">
                                <p class="font-semibold text-zinc-950 dark:text-white">{{ $transaction->title }}</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $transaction->sourceUser?->name ?? 'System' }}</p>
                                <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">{{ $transaction->posted_at->format('d M Y') }}</p>
                            </div>

                            <p class="text-lg font-semibold {{ $transaction->direction === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $transaction->direction === 'credit' ? '+' : '-' }}৳{{ number_format((float) $transaction->amount, 2) }}
                            </p>
                        </div>
                    @empty
                        <div class="app-empty-state">
                            এখনো কোনো earnings activity নেই।
                        </div>
                    @endforelse
                </div>

                <div class="app-table-wrap mt-5 hidden md:block">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50 text-left text-zinc-500 dark:bg-zinc-800/70 dark:text-zinc-400">
                            <tr>
                                <th class="px-4 py-3 font-medium">Title</th>
                                <th class="px-4 py-3 font-medium">From</th>
                                <th class="px-4 py-3 font-medium">Date</th>
                                <th class="px-4 py-3 text-right font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($recentTransactions as $transaction)
                                <tr class="bg-white/85 dark:bg-zinc-900/70">
                                    <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $transaction->title }}</td>
                                    <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $transaction->sourceUser?->name ?? 'System' }}</td>
                                    <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $transaction->posted_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold {{ $transaction->direction === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $transaction->direction === 'credit' ? '+' : '-' }}৳{{ number_format((float) $transaction->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">এখনো কোনো earnings activity নেই।</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-app-panel>

            <x-app-panel>
                <x-app-section-heading
                    title="Recent Invoices"
                    description="Package billing and paid invoice history."
                    eyebrow="Billing"
                >
                    <x-slot:actions>
                        <a href="{{ route('mlm.invoices') }}" class="app-inline-link" wire:navigate>Open invoices</a>
                    </x-slot:actions>
                </x-app-section-heading>

                <div class="mt-5 app-list-stack">
                    @forelse ($recentInvoices as $invoice)
                        <div class="app-list-row">
                            <div class="space-y-2">
                                <p class="font-semibold text-zinc-950 dark:text-white">{{ $invoice->title }}</p>
                                <div class="flex flex-wrap gap-3 text-sm text-zinc-500 dark:text-zinc-400">
                                    <span>{{ $invoice->invoice_no }}</span>
                                    <span>{{ $invoice->issued_at?->format('d M Y') }}</span>
                                </div>
                            </div>

                            <div class="text-left sm:text-right">
                                <p class="text-lg font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $invoice->amount, 2) }}</p>
                                <span class="mt-2 inline-flex rounded-full bg-emerald-500/12 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700 dark:text-emerald-300">
                                    {{ $invoice->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="app-empty-state">
                            এখনো কোনো invoice নেই। Package activation বা retail order-এর পরে invoice history এখানে দেখা যাবে।
                        </div>
                    @endforelse
                </div>
            </x-app-panel>
        </section>
    </x-app-page>
</x-layouts::app>
