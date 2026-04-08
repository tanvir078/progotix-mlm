<x-layouts::app :title="__('Reports')">
    <div class="flex flex-col gap-6">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm text-zinc-500 dark:text-zinc-400">Members</p><p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $totals['members'] }}</p></div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm text-zinc-500 dark:text-zinc-400">Binary Placements</p><p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $totals['binary_nodes'] }}</p></div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm text-zinc-500 dark:text-zinc-400">Level Commission</p><p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $totals['team_commission'], 2) }}</p></div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm text-zinc-500 dark:text-zinc-400">Binary Bonus</p><p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $totals['binary_bonus_paid'], 2) }}</p></div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm text-zinc-500 dark:text-zinc-400">Approved Withdrawals</p><p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $totals['withdrawal_approved'], 2) }}</p></div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm text-zinc-500 dark:text-zinc-400">Carry Forward</p><p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $totals['carry_forward'], 2) }}</p></div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Plan Performance</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($planPerformance as $plan)
                        <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $plan->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $plan->code }} • {{ $plan->subscriptions_count }} subscriptions</p>
                                </div>
                                <p class="text-sm font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $plan->revenue, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Top Earners</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($topEarners as $member)
                        <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $member->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ '@'.$member->username }}</p>
                                </div>
                                <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">৳{{ number_format((float) $member->balance, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Monthly Revenue</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($monthlyRevenue as $month)
                        <div class="flex items-center justify-between rounded-2xl bg-zinc-50 px-4 py-3 dark:bg-zinc-800/60">
                            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $month->month }}</span>
                            <span class="text-sm font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $month->total, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No monthly revenue data yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Status Breakdown</h2>
                <div class="mt-5 space-y-3">
                    <div class="flex items-center justify-between rounded-2xl bg-zinc-50 px-4 py-3 dark:bg-zinc-800/60"><span class="text-sm text-zinc-600 dark:text-zinc-300">Active subscriptions</span><span class="text-sm font-semibold text-zinc-950 dark:text-white">{{ $statusBreakdown['active_subscriptions'] }}</span></div>
                    <div class="flex items-center justify-between rounded-2xl bg-zinc-50 px-4 py-3 dark:bg-zinc-800/60"><span class="text-sm text-zinc-600 dark:text-zinc-300">Pending withdrawals</span><span class="text-sm font-semibold text-zinc-950 dark:text-white">{{ $statusBreakdown['pending_withdrawals'] }}</span></div>
                    <div class="flex items-center justify-between rounded-2xl bg-zinc-50 px-4 py-3 dark:bg-zinc-800/60"><span class="text-sm text-zinc-600 dark:text-zinc-300">Paid invoices</span><span class="text-sm font-semibold text-zinc-950 dark:text-white">{{ $statusBreakdown['paid_invoices'] }}</span></div>
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
