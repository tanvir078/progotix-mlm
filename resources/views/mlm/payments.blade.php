<x-layouts::app :title="__('Payments')">
    @php
        $paymentCards = [
            [
                'label' => 'Deposit Channels',
                'value' => number_format((int) $depositStats['method_count']),
                'meta' => 'Available for your country',
                'icon' => 'credit-card',
                'tone' => 'brand',
            ],
            [
                'label' => 'Pending Deposits',
                'value' => '$'.number_format((float) $depositStats['pending_total'], 2),
                'meta' => 'Awaiting admin review',
                'icon' => 'clock',
                'tone' => 'accent',
            ],
            [
                'label' => 'Approved Credits',
                'value' => '$'.number_format((float) $depositStats['approved_total'], 2),
                'meta' => 'Net funded to wallet',
                'icon' => 'banknotes',
            ],
            [
                'label' => 'Global Channels',
                'value' => number_format((int) $depositStats['global_method_count']),
                'meta' => 'Cross-border access',
                'icon' => 'globe-alt',
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
                <p class="font-medium">Deposit request review করুন।</p>
                <p class="mt-1 text-sm">{{ $errors->first() }}</p>
            </div>
        @endif

        <x-app-panel variant="hero" padding="hero">
            <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr] xl:items-end">
                <div class="space-y-4">
                    <p class="app-kicker">Multi Payment Engine</p>
                    <div class="space-y-4">
                        <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-white sm:text-4xl xl:text-[2.6rem]">
                            Country-aware payment desk for e-wallet, bank, card, and crypto funding.
                        </h1>
                        <p class="max-w-2xl text-sm leading-7 text-white/82 sm:text-base">
                            আপনার country profile অনুযায়ী available channel দেখাবে, member deposit submit করবে, আর admin approve করার পরেই wallet credit হবে।
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/62">Coverage</p>
                        <p class="mt-3 text-3xl font-semibold text-white">{{ number_format((int) $depositStats['method_count']) }}</p>
                        <p class="mt-2 text-sm text-white/72">Filtered payment methods for your member country.</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/58">Quick Access</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ route('mlm.wallet') }}" class="app-pill app-pill-secondary" wire:navigate>Wallet</a>
                            <a href="{{ route('mlm.withdrawals.index') }}" class="app-pill app-pill-primary" wire:navigate>Cashout</a>
                        </div>
                    </div>
                </div>
            </div>
        </x-app-panel>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($paymentCards as $card)
                <x-app-stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :meta="$card['meta']"
                    :icon="$card['icon']"
                    :tone="$card['tone'] ?? 'default'"
                />
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <x-app-panel>
                <x-app-section-heading
                    title="Submit Deposit"
                    description="Reference, sender details, and optional payment proof দিয়ে request submit করুন।"
                    eyebrow="Funding Form"
                />

                <form method="POST" action="{{ route('mlm.payments.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label class="app-form-label">Payment Method</label>
                        <select name="payment_method_id" class="app-form-control">
                            <option value="">Select a funding channel</option>
                            @foreach ($availableMethods as $method)
                                <option value="{{ $method->id }}" @selected((int) old('payment_method_id') === $method->id)>
                                    {{ $method->name }} • {{ $method->type_label }} • {{ $method->currency_code }} • {{ $method->country_label }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method_id')
                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="app-form-label">Amount</label>
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

                        <div>
                            <label class="app-form-label">Transaction Reference</label>
                            <input
                                name="transaction_reference"
                                type="text"
                                value="{{ old('transaction_reference') }}"
                                class="app-form-control"
                                placeholder="UTR, TxID, bank ref, or processor ref"
                            />
                            @error('transaction_reference')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="app-form-label">Sender Name</label>
                            <input
                                name="sender_name"
                                type="text"
                                value="{{ old('sender_name') }}"
                                class="app-form-control"
                                placeholder="Account holder or wallet owner"
                            />
                        </div>

                        <div>
                            <label class="app-form-label">Sender Account</label>
                            <input
                                name="sender_account"
                                type="text"
                                value="{{ old('sender_account') }}"
                                class="app-form-control"
                                placeholder="Phone, account no, wallet ID, or card suffix"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="app-form-label">Payment Proof</label>
                        <input name="payment_proof" type="file" class="app-form-control" />
                        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">JPG, PNG, WEBP, or PDF up to 5MB.</p>
                        @error('payment_proof')
                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="app-form-label">Note</label>
                        <textarea
                            name="note"
                            rows="4"
                            class="app-form-control app-form-textarea"
                            placeholder="Optional deposit note"
                        >{{ old('note') }}</textarea>
                    </div>

                    <button type="submit" class="app-button-primary w-full">
                        Submit deposit request
                    </button>
                </form>
            </x-app-panel>

            <div class="grid gap-6">
                <x-app-panel variant="contrast">
                    <x-app-section-heading
                        title="Available Channels"
                        description="Methods are grouped by type and filtered by your country profile."
                        eyebrow="Coverage Map"
                    />

                    <div class="mt-5 space-y-5">
                        @forelse ($methodGroups as $type => $methods)
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-zinc-500 dark:text-zinc-400">
                                        {{ $paymentTypeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)) }}
                                    </p>
                                    <span class="app-status-badge app-status-badge-neutral">{{ $methods->count() }} methods</span>
                                </div>

                                <div class="app-list-stack">
                                    @foreach ($methods as $method)
                                        @php
                                            $chargeRate = number_format((float) $method->percent_charge_rate * 100, 2);
                                        @endphp
                                        <div class="app-list-row">
                                            <div class="space-y-2">
                                                <div class="flex flex-wrap items-center gap-3">
                                                    <p class="font-semibold text-zinc-950 dark:text-white">{{ $method->name }}</p>
                                                    <span class="app-status-badge app-status-badge-neutral">{{ $method->country_label }}</span>
                                                </div>
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $method->provider_name ?: 'Manual review' }} • {{ $method->currency_code }} • Min {{ number_format((float) $method->min_amount, 2) }}
                                                    @if ($method->max_amount !== null)
                                                        • Max {{ number_format((float) $method->max_amount, 2) }}
                                                    @endif
                                                </p>
                                                @if ($method->destination_label && $method->destination_value)
                                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $method->destination_label }}: {{ $method->destination_value }}</p>
                                                @endif
                                                @if ($method->instructions)
                                                    <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $method->instructions }}</p>
                                                @endif
                                            </div>

                                            <div class="text-left sm:text-right">
                                                <p class="font-semibold text-zinc-950 dark:text-white">{{ $chargeRate }}% + {{ number_format((float) $method->fixed_charge, 2) }}</p>
                                                <p class="mt-1 text-xs uppercase tracking-[0.22em] text-zinc-400">Charge Model</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="app-empty-state">
                                আপনার country profile-এর জন্য এখনো কোনো payment channel configured নেই।
                            </div>
                        @endforelse
                    </div>
                </x-app-panel>

                <x-app-panel>
                    <x-app-section-heading
                        title="Deposit History"
                        description="Every funding request with method, net credit, and approval trail."
                        eyebrow="History"
                    />

                    <div class="mt-5 app-list-stack">
                        @forelse ($requests as $deposit)
                            @php
                                $statusClass = match ($deposit->status) {
                                    'approved' => 'app-status-badge app-status-badge-success',
                                    'rejected' => 'app-status-badge app-status-badge-danger',
                                    default => 'app-status-badge app-status-badge-warning',
                                };
                            @endphp
                            <div class="app-list-row">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="font-semibold text-zinc-950 dark:text-white">{{ $deposit->payment_method_name }}</p>
                                        <span class="{{ $statusClass }}">{{ $deposit->status }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Ref: {{ $deposit->transaction_reference }} • {{ $deposit->submitted_at?->format('d M Y, h:i A') ?? $deposit->created_at->format('d M Y, h:i A') }}
                                    </p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Gross {{ number_format((float) $deposit->amount, 2) }} {{ $deposit->currency }} • Net {{ number_format((float) $deposit->net_amount, 2) }} {{ $deposit->currency }}
                                    </p>
                                    @if ($deposit->admin_note)
                                        <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">Admin note: {{ $deposit->admin_note }}</p>
                                    @endif
                                </div>

                                <div class="text-left sm:text-right">
                                    <p class="font-semibold text-zinc-950 dark:text-white">{{ $deposit->currency }}</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.22em] text-zinc-400">
                                        Charge {{ number_format((float) $deposit->charge_amount, 2) }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="app-empty-state">
                                এখনো কোনো deposit request পাওয়া যায়নি।
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-5">{{ $requests->links() }}</div>
                </x-app-panel>
            </div>
        </section>
    </x-app-page>
</x-layouts::app>
