<x-layouts::app :title="__('Withdrawals')">
    <div class="flex flex-col gap-6">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Wallet Balance</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $walletBalance, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pending Requests</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $pendingTotal, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Available to Withdraw</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $availableBalance, 2) }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Request payout</h2>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Admin approval-এর পর amount wallet থেকে debit হবে।</p>

                <form method="POST" action="{{ route('mlm.withdrawals.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Amount</label>
                        <input name="amount" type="number" min="1" step="0.01" value="{{ old('amount') }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-hidden ring-0 transition focus:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white" />
                        @error('amount')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Payment method</label>
                        <input name="payment_method" type="text" value="{{ old('payment_method') }}" placeholder="Bank, bKash, Nagad" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-hidden transition focus:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white" />
                        @error('payment_method')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Account details</label>
                        <textarea name="account_details" rows="4" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-hidden transition focus:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">{{ old('account_details') }}</textarea>
                        @error('account_details')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-200">Note</label>
                        <textarea name="note" rows="3" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-hidden transition focus:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">{{ old('note') }}</textarea>
                    </div>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-4 py-3 text-sm font-medium text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200">
                        Submit withdrawal request
                    </button>
                </form>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Withdrawal history</h2>
                <div class="mt-5 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
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
                                <tr>
                                    <td class="px-4 py-4 font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $withdrawal->amount, 2) }}</td>
                                    <td class="px-4 py-4 text-zinc-500 dark:text-zinc-400">{{ $withdrawal->payment_method }}</td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium uppercase tracking-[0.2em] text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $withdrawal->status }}
                                        </span>
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
            </div>
        </section>
    </div>
</x-layouts::app>
