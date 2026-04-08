<x-layouts::app :title="__('Earnings Ledger')">
    <div class="flex flex-col gap-6">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Wallet Balance</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $totals['wallet_balance'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Direct Bonus</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $totals['direct_bonus'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">This Month</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $totals['monthly_earnings'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Entries</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $totals['total_transactions'] }}</p>
            </div>
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Commission ledger</h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">প্রতিটি bonus entry, source member, আর related package এখানে দেখা যাবে।</p>
                </div>
            </div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-left text-zinc-500 dark:bg-zinc-800/70 dark:text-zinc-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Entry</th>
                            <th class="px-4 py-3 font-medium">Source</th>
                            <th class="px-4 py-3 font-medium">Package</th>
                            <th class="px-4 py-3 font-medium">Posted</th>
                            <th class="px-4 py-3 text-right font-medium">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td class="px-4 py-4">
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $transaction->title }}</p>
                                    @if ($transaction->note)
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $transaction->note }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-zinc-500 dark:text-zinc-400">{{ $transaction->sourceUser?->name ?? 'System' }}</td>
                                <td class="px-4 py-4 text-zinc-500 dark:text-zinc-400">{{ $transaction->subscription?->plan?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-4 text-zinc-500 dark:text-zinc-400">{{ $transaction->posted_at->format('d M Y, h:i A') }}</td>
                                <td class="px-4 py-4 text-right font-semibold {{ $transaction->direction === 'credit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $transaction->direction === 'credit' ? '+' : '-' }}৳{{ number_format((float) $transaction->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">কোনো commission history পাওয়া যায়নি।</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                {{ $transactions->links() }}
            </div>
        </section>
    </div>
</x-layouts::app>
