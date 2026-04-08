<x-layouts::app :title="__('MLM Dashboard')">
    <div class="flex flex-col gap-6">
        <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-linear-to-br from-emerald-600 via-teal-600 to-cyan-700 p-6 text-white shadow-sm dark:border-zinc-700">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl space-y-3">
                    <p class="text-sm font-medium uppercase tracking-[0.3em] text-white/70">ProgotiX MLM</p>
                    <h1 class="text-3xl font-semibold tracking-tight">নেটওয়ার্ক, কমিশন আর প্যাকেজ সব এক জায়গায়</h1>
                    <p class="max-w-xl text-sm leading-6 text-white/80">
                        আপনার sponsor chain, active package, referral income আর recent activity এখান থেকে track করতে পারবেন।
                    </p>
                </div>

                <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs uppercase tracking-[0.3em] text-white/70">Referral Link</p>
                    <p class="mt-2 break-all text-sm font-medium">{{ $user->referral_link }}</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Wallet Balance</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $stats['wallet_balance'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Team Size</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $stats['team_size'] }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Binary Team</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $stats['binary_team_size'] }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Direct Referrals</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $stats['direct_referrals'] }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Direct Bonus Total</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $stats['direct_bonus_total'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Binary Bonus Total</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $stats['binary_bonus_total'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">This Month</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $stats['monthly_earnings'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pending Withdrawals</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $stats['pending_withdrawals'], 2) }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Direct Network</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">আপনার নিচে সরাসরি যোগ হওয়া সদস্যরা</p>
                    </div>
                    <a href="{{ route('mlm.network') }}" class="text-sm font-medium text-teal-600 dark:text-teal-400">View full network</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($directReferrals as $member)
                        <div class="flex flex-col gap-3 rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="font-medium text-zinc-950 dark:text-white">{{ $member->name }}</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ '@'.$member->username }} • {{ $member->member_code }}</p>
                            </div>
                            <div class="flex items-center gap-6 text-sm text-zinc-600 dark:text-zinc-300">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.25em] text-zinc-400">Downline</p>
                                    <p class="mt-1 font-semibold">{{ $member->referrals_count }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-[0.25em] text-zinc-400">Wallet</p>
                                    <p class="mt-1 font-semibold">৳{{ number_format((float) $member->balance, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            এখনো কোনো direct referral নেই। আপনার referral link share করলেই নতুন সদস্যরা এই তালিকায় আসবে।
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Current Package</h2>
                    @if ($activeSubscription)
                        <div class="mt-4 rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/60">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $activeSubscription->plan->name }}</p>
                            <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $activeSubscription->amount, 2) }}</p>
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Activated {{ $activeSubscription->started_at->diffForHumans() }}</p>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-zinc-500 dark:text-zinc-400">এখনো কোনো package activate করা হয়নি। নিচ থেকে একটি package চালু করুন।</p>
                    @endif
                </div>

                <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Available Packages</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">দ্রুত activation-এর জন্য</p>
                        </div>
                        <a href="{{ route('mlm.plans.index') }}" class="text-sm font-medium text-teal-600 dark:text-teal-400">All packages</a>
                    </div>

                    <div class="mt-5 space-y-3">
                        @foreach ($plans->take(3) as $plan)
                            <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-zinc-950 dark:text-white">{{ $plan->name }}</p>
                                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $plan->description }}</p>
                                    </div>
                                    <p class="text-lg font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $plan->price, 2) }}</p>
                                </div>
                                <p class="mt-3 text-sm text-emerald-600 dark:text-emerald-400">Direct bonus: ৳{{ number_format((float) $plan->direct_bonus, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Recent Earnings Activity</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">সর্বশেষ bonus এবং wallet movement</p>
                    </div>
                    <a href="{{ route('mlm.earnings') }}" class="text-sm font-medium text-teal-600 dark:text-teal-400">Open ledger</a>
                </div>

                <div class="mt-5 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
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
                                <tr class="bg-white dark:bg-zinc-900">
                                    <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $transaction->title }}</td>
                                    <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $transaction->sourceUser?->name ?? 'System' }}</td>
                                    <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $transaction->posted_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">
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
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Recent Invoices</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">সর্বশেষ package billing record</p>
                    </div>
                    <a href="{{ route('mlm.invoices') }}" class="text-sm font-medium text-teal-600 dark:text-teal-400">Open invoices</a>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($recentInvoices as $invoice)
                        <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $invoice->invoice_no }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $invoice->title }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $invoice->amount, 2) }}</p>
                                    <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">{{ $invoice->status }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            এখনো কোনো invoice নেই।
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
