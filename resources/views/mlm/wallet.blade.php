<x-layouts::app :title="__('Wallet')">
    @php
        $walletStats = [
            [
                'label' => 'Wallet Balance',
                'value' => '$'.number_format((float) $walletBalance, 2),
                'meta' => 'Gross wallet',
                'icon' => 'wallet',
                'tone' => 'brand',
            ],
            [
                'label' => 'Available Balance',
                'value' => '$'.number_format((float) $availableBalance, 2),
                'meta' => 'Transfer ready',
                'icon' => 'banknotes',
                'tone' => 'accent',
            ],
            [
                'label' => 'Pending Withdrawals',
                'value' => '$'.number_format((float) $pendingTotal, 2),
                'meta' => 'Reserved payout',
                'icon' => 'arrows-right-left',
            ],
            [
                'label' => 'Transfer Fee',
                'value' => number_format((float) ($feeRate * 100), 2).'%',
                'meta' => 'Internal transfer charge',
                'icon' => 'receipt-percent',
            ],
        ];
    @endphp

    <x-app-page spacing="loose">
        @if (session('status'))
            <div class="app-alert app-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="app-alert app-alert-danger">
                <p class="font-medium">Wallet action review করুন।</p>
                <p class="mt-1 text-sm">{{ $errors->first() }}</p>
            </div>
        @endif

        <x-app-panel variant="hero" padding="hero">
            <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr] xl:items-end">
                <div class="space-y-4">
                    <p class="app-kicker">Central Wallet Engine</p>
                    <div class="space-y-4">
                        <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-white sm:text-4xl xl:text-[2.6rem]">
                            One mobile-friendly balance desk for transfers, payout readiness, and currency-aware cash visibility.
                        </h1>
                        <p class="max-w-2xl text-sm leading-7 text-white/82 sm:text-base">
                            Commission, bonus, transfer, আর payout reserve একসাথে monitor করার জন্য wallet screen-টা action-first structure-এ রাখা হয়েছে।
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/62">Available to Move</p>
                        <p class="mt-3 text-3xl font-semibold text-white">${{ number_format((float) $availableBalance, 2) }}</p>
                        <p class="mt-2 text-sm text-white/72">Pending withdrawal reserve বাদ দিয়ে usable balance।</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/58">Quick Access</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ route('mlm.payments.index') }}" class="app-pill app-pill-secondary" wire:navigate>Payments</a>
                            <a href="{{ route('mlm.withdrawals.index') }}" class="app-pill app-pill-secondary" wire:navigate>Cashout</a>
                            <a href="{{ route('mlm.earnings') }}" class="app-pill app-pill-primary" wire:navigate>Ledger</a>
                        </div>
                    </div>
                </div>
            </div>
        </x-app-panel>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($walletStats as $stat)
                <x-app-stat-card
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :meta="$stat['meta']"
                    :icon="$stat['icon']"
                    :tone="$stat['tone'] ?? 'default'"
                />
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <x-app-panel>
                <x-app-section-heading
                    title="Transfer Funds"
                    description="Internal member-to-member transfer with fee-aware validation."
                    eyebrow="Send Balance"
                />

                <form method="POST" action="{{ route('mlm.wallet.transfer') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label class="app-form-label">Receiver Username or Member Code</label>
                        <input
                            name="receiver_identity"
                            type="text"
                            value="{{ old('receiver_identity', old('receiver_username')) }}"
                            class="app-form-control"
                            placeholder="Enter username or PGX member code"
                        />
                        <p class="mt-2 text-xs uppercase tracking-[0.2em] text-zinc-400">Examples: `rahim123` or `PGX-AB12CDE`</p>
                        @error('receiver_identity')
                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="app-form-label">Amount (USD)</label>
                            <input
                                name="amount"
                                type="number"
                                min="1"
                                step="0.01"
                                value="{{ old('amount') }}"
                                class="app-form-control"
                                placeholder="0.00"
                            />
                            @error('amount')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="app-metric-tile">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Fee Preview</p>
                            <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format((float) ($feeRate * 100), 2) }}%</p>
                            <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                Total debit হবে amount + fee, তাই available balance মাথায় রাখুন।
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="app-form-label">Note</label>
                        <textarea
                            name="note"
                            rows="4"
                            class="app-form-control app-form-textarea"
                            placeholder="Optional transfer note"
                        >{{ old('note') }}</textarea>
                    </div>

                    <button type="submit" class="app-button-primary w-full">
                        Send wallet transfer
                    </button>
                </form>
            </x-app-panel>

            <div class="grid gap-6">
                <x-app-panel variant="contrast">
                    <x-app-section-heading
                        title="Multi-currency View"
                        description="Available balance converted for quick reading across supported currencies."
                        eyebrow="Currency"
                    >
                        <x-slot:actions>
                            <a href="{{ route('mlm.payments.index') }}" class="app-inline-link" wire:navigate>Payments</a>
                        </x-slot:actions>
                    </x-app-section-heading>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($currencies as $currency)
                            <div class="app-metric-tile">
                                <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">{{ $currency['code'] }}</p>
                                <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $currency['converted'], 2) }}</p>
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $currency['label'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        <div class="app-metric-tile">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Currencies</p>
                            <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ number_format((int) $transferStats['currency_count']) }}</p>
                        </div>
                        <div class="app-metric-tile">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Outgoing Volume</p>
                            <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">${{ number_format((float) $transferStats['outgoing_volume'], 2) }}</p>
                        </div>
                        <div class="app-metric-tile">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Incoming Net</p>
                            <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">${{ number_format((float) $transferStats['incoming_volume'], 2) }}</p>
                        </div>
                        <div class="app-metric-tile sm:col-span-3">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Payment Channels</p>
                            <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ number_format((int) $transferStats['payment_channel_count']) }}</p>
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                Global channels {{ number_format((int) $transferStats['global_channel_count']) }}। Country scoped methods member profile অনুযায়ী filter হচ্ছে।
                            </p>
                        </div>
                    </div>
                </x-app-panel>

                <x-app-panel>
                    <x-app-section-heading
                        title="Payment Channels"
                        description="Configured methods available across deposit and withdrawal operations."
                        eyebrow="Payment Setup"
                    />

                    <div class="mt-5 app-list-stack">
                        @forelse ($paymentChannels as $method)
                            <div class="app-list-row">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 inline-flex size-6 items-center justify-center rounded-full bg-teal-500/12 text-teal-700 dark:text-teal-300">
                                        <flux:icon name="credit-card" class="size-4" />
                                    </span>
                                    <div class="space-y-1">
                                        <p class="text-sm font-semibold text-zinc-950 dark:text-white">{{ $method->name }}</p>
                                        <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                                            {{ $method->type_label }} • {{ $method->country_label }} • {{ $method->currency_code }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if ($method->supports_deposit)
                                        <span class="app-status-badge app-status-badge-success">Deposit</span>
                                    @endif
                                    @if ($method->supports_withdrawal)
                                        <span class="app-status-badge app-status-badge-warning">Withdrawal</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="app-empty-state">
                                এখনো কোনো payment channel configured নেই।
                            </div>
                        @endforelse
                    </div>
                </x-app-panel>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <x-app-panel>
                <x-app-section-heading
                    title="Recent Transfers"
                    description="Latest outgoing member-to-member wallet movements."
                    eyebrow="Outgoing"
                />

                <div class="mt-5 app-list-stack">
                    @forelse ($recentTransfers as $transfer)
                        <div class="app-list-row">
                            <div class="space-y-1">
                                <p class="font-semibold text-zinc-950 dark:text-white">Sent to {{ '@'.$transfer->receiver->username }}</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $transfer->transferred_at?->format('d M Y, h:i A') ?? 'Date not set' }}
                                </p>
                                @if ($transfer->note)
                                    <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $transfer->note }}</p>
                                @endif
                            </div>

                            <div class="text-left sm:text-right">
                                <p class="font-semibold text-zinc-950 dark:text-white">${{ number_format((float) $transfer->amount, 2) }}</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.22em] text-zinc-400">
                                    Fee ${{ number_format((float) $transfer->fee, 2) }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="app-empty-state">
                            No outgoing transfer yet. Receiver username দিয়ে transfer করলে history এখানে দেখা যাবে।
                        </div>
                    @endforelse
                </div>
            </x-app-panel>

            <x-app-panel>
                <x-app-section-heading
                    title="Incoming Transfers"
                    description="Latest incoming credits after fee deduction."
                    eyebrow="Incoming"
                />

                <div class="mt-5 app-list-stack">
                    @forelse ($recentIncomingTransfers as $transfer)
                        <div class="app-list-row">
                            <div class="space-y-1">
                                <p class="font-semibold text-zinc-950 dark:text-white">Received from {{ '@'.$transfer->sender->username }}</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $transfer->transferred_at?->format('d M Y, h:i A') ?? 'Date not set' }}
                                </p>
                                @if ($transfer->note)
                                    <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $transfer->note }}</p>
                                @endif
                            </div>

                            <div class="text-left sm:text-right">
                                <p class="font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format((float) $transfer->net_amount, 2) }}</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.22em] text-zinc-400">
                                    Gross ${{ number_format((float) $transfer->amount, 2) }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="app-empty-state">
                            No incoming transfer yet. Member support transfer এলে net amount এখানে দেখাবে।
                        </div>
                    @endforelse
                </div>
            </x-app-panel>
        </section>
    </x-app-page>
</x-layouts::app>
