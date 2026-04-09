<x-layouts::app :title="__('Withdrawals')">
    @php
        $withdrawalCards = [
            [
                'label' => 'Wallet Balance',
                'value' => '৳'.number_format((float) $walletBalance, 2),
                'meta' => 'Gross wallet',
                'icon' => 'wallet',
                'tone' => 'brand',
            ],
            [
                'label' => 'Pending Requests',
                'value' => '৳'.number_format((float) $pendingTotal, 2),
                'meta' => 'Awaiting approval',
                'icon' => 'clock',
                'tone' => 'accent',
            ],
            [
                'label' => 'Available to Withdraw',
                'value' => '৳'.number_format((float) $availableBalance, 2),
                'meta' => 'Usable now',
                'icon' => 'banknotes',
            ],
            [
                'label' => 'Approved Total',
                'value' => '৳'.number_format((float) $withdrawalStats['approved_total'], 2),
                'meta' => 'Paid out already',
                'icon' => 'check-badge',
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
                <p class="font-medium">Withdrawal request review করুন।</p>
                <p class="mt-1 text-sm">{{ $errors->first() }}</p>
            </div>
        @endif

        <x-app-panel variant="hero" padding="hero">
            <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr] xl:items-end">
                <div class="space-y-4">
                    <p class="app-kicker">Payout Workflow</p>
                    <div class="space-y-4">
                        <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-white sm:text-4xl xl:text-[2.6rem]">
                            Clean mobile payout desk for request entry, reserve awareness, and approval-ready cashout history.
                        </h1>
                        <p class="max-w-2xl text-sm leading-7 text-white/82 sm:text-base">
                            Withdrawal amount submit করার আগে wallet reserve, payout method, আর approval workflow clearভাবে দেখানোর জন্য screen-টা restructure করা হয়েছে।
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/62">Request Count</p>
                        <p class="mt-3 text-3xl font-semibold text-white">{{ number_format((int) $withdrawalStats['request_count']) }}</p>
                        <p class="mt-2 text-sm text-white/72">All submitted payout requests.</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/58">Configured Methods</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            @foreach ($payoutMethods as $method)
                                <span class="app-pill app-pill-primary">{{ $method }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </x-app-panel>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($withdrawalCards as $card)
                <x-app-stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :meta="$card['meta']"
                    :icon="$card['icon']"
                    :tone="$card['tone'] ?? 'default'"
                />
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
            <x-app-panel>
                <x-app-section-heading
                    title="Request Payout"
                    description="Admin approval-এর পর amount wallet থেকে debit হবে।"
                    eyebrow="Cashout Form"
                />

                <form method="POST" action="{{ route('mlm.withdrawals.store') }}" class="mt-6 space-y-4">
                    @csrf

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
                        <label class="app-form-label">Payment Method</label>
                        <select name="payment_method_id" class="app-form-control">
                            <option value="">Select payout method</option>
                            @foreach ($payoutMethods as $method)
                                <option value="{{ $method->id }}" @selected((int) old('payment_method_id') === $method->id)>
                                    {{ $method->name }} • {{ $method->type_label }} • {{ $method->currency_code }} • {{ $method->country_label }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method_id')
                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="app-form-label">Account Details</label>
                        <textarea
                            name="account_details"
                            rows="4"
                            class="app-form-control app-form-textarea"
                            placeholder="Account number, wallet ID, branch, routing, or payout instruction"
                        >{{ old('account_details') }}</textarea>
                        @error('account_details')
                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="app-form-label">Note</label>
                        <textarea
                            name="note"
                            rows="3"
                            class="app-form-control"
                            placeholder="Optional payout note"
                        >{{ old('note') }}</textarea>
                    </div>

                    <button type="submit" class="app-button-primary w-full">
                        Submit withdrawal request
                    </button>
                </form>
            </x-app-panel>

            <div class="grid gap-6">
                <x-app-panel variant="contrast">
                    <x-app-section-heading
                        title="Payout Readiness"
                        description="What the current wallet and request mix means for cashout operations."
                        eyebrow="Insights"
                    />

                    <div class="mt-5 app-list-stack">
                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Method availability is country-aware</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    Available channels: {{ number_format((int) $withdrawalStats['method_count']) }}। E-wallet, bank, card, আর crypto method admin config অনুযায়ী filter হচ্ছে।
                                </p>
                            </div>
                        </div>
                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Pending requests reserve part of your wallet</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    Available balance সবসময় pending payout reserve বাদ দিয়ে calculate হচ্ছে।
                                </p>
                            </div>
                        </div>
                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Approval happens after admin review</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    Request submit হলেই wallet debit হচ্ছে না, approve হলে final debit হবে।
                                </p>
                            </div>
                        </div>
                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Rejected requests stay visible for audit</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    Rejected count: {{ number_format((int) $withdrawalStats['rejected_count']) }}।
                                </p>
                            </div>
                        </div>
                    </div>
                </x-app-panel>

                <x-app-panel>
                    <x-app-section-heading
                        title="Withdrawal History"
                        description="Every payout request with method, status, and request timing."
                        eyebrow="History"
                    />

                    <div class="mt-5 space-y-3 md:hidden">
                        @forelse ($requests as $withdrawal)
                            @php
                                $statusClass = match ($withdrawal->status) {
                                    'approved' => 'app-status-badge app-status-badge-success',
                                    'rejected' => 'app-status-badge app-status-badge-danger',
                                    default => 'app-status-badge app-status-badge-warning',
                                };
                            @endphp
                            <div class="app-list-row">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $withdrawal->amount, 2) }}</p>
                                        <span class="{{ $statusClass }}">{{ $withdrawal->status }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $withdrawal->payment_method }}</p>
                                    @if ($withdrawal->paymentMethod)
                                        <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">{{ $withdrawal->paymentMethod->type_label }} • {{ $withdrawal->paymentMethod->country_label }}</p>
                                    @endif
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">{{ $withdrawal->created_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="app-empty-state">
                                কোনো withdrawal request পাওয়া যায়নি।
                            </div>
                        @endforelse
                    </div>

                    <div class="app-table-wrap mt-5 hidden md:block">
                        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                            <thead class="bg-zinc-50 text-left text-zinc-500 dark:bg-zinc-800/70 dark:text-zinc-400">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Amount</th>
                                    <th class="px-4 py-3 font-medium">Method</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium">Requested</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @forelse ($requests as $withdrawal)
                                    @php
                                        $statusClass = match ($withdrawal->status) {
                                            'approved' => 'app-status-badge app-status-badge-success',
                                            'rejected' => 'app-status-badge app-status-badge-danger',
                                            default => 'app-status-badge app-status-badge-warning',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-4 font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $withdrawal->amount, 2) }}</td>
                                        <td class="px-4 py-4 text-zinc-500 dark:text-zinc-400">
                                            <div>{{ $withdrawal->payment_method }}</div>
                                            @if ($withdrawal->paymentMethod)
                                                <div class="mt-1 text-xs uppercase tracking-[0.22em] text-zinc-400">{{ $withdrawal->paymentMethod->type_label }} • {{ $withdrawal->paymentMethod->country_label }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="{{ $statusClass }}">{{ $withdrawal->status }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-zinc-500 dark:text-zinc-400">{{ $withdrawal->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">কোনো withdrawal request পাওয়া যায়নি।</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-5">{{ $requests->links() }}</div>
                </x-app-panel>
            </div>
        </section>
    </x-app-page>
</x-layouts::app>
