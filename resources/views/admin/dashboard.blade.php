<x-layouts::app :title="__('Admin Dashboard')">
    <div class="flex flex-col gap-6">
        <section class="rounded-3xl border border-zinc-200 bg-linear-to-br from-slate-900 via-slate-800 to-cyan-900 p-6 text-white shadow-sm dark:border-zinc-700">
            <p class="text-sm uppercase tracking-[0.3em] text-cyan-200/70">Admin Command Center</p>
            <h1 class="mt-3 text-3xl font-semibold">MLM অপারেশন, payout queue আর revenue এক নজরে</h1>
            <p class="mt-3 max-w-3xl text-sm text-white/80">এখান থেকে member growth, invoices, commission payout এবং withdrawal approvals monitor করতে পারবেন।</p>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm text-zinc-500 dark:text-zinc-400">Members</p><p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $stats['members'] }}</p></div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm text-zinc-500 dark:text-zinc-400">Active Packages</p><p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $stats['active_packages'] }}</p></div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm text-zinc-500 dark:text-zinc-400">Paid Invoices</p><p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $stats['paid_invoices'], 2) }}</p></div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm text-zinc-500 dark:text-zinc-400">Pending Withdrawals</p><p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $stats['pending_withdrawals'], 2) }}</p></div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><p class="text-sm text-zinc-500 dark:text-zinc-400">Commissions Paid</p><p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $stats['commissions_paid'], 2) }}</p></div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Recent Members</h2>
                    <a href="{{ route('admin.members') }}" class="text-sm font-medium text-teal-600 dark:text-teal-400">Open member list</a>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach ($recentMembers as $member)
                        <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $member->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ '@'.$member->username }} • Sponsor: {{ $member->referrer?->username ?? 'N/A' }}</p>
                                </div>
                                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $member->subscriptions->first()?->plan?->name ?? 'No plan' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Recent Withdrawal Requests</h2>
                    <a href="{{ route('admin.withdrawals') }}" class="text-sm font-medium text-teal-600 dark:text-teal-400">Open queue</a>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach ($recentWithdrawals as $withdrawal)
                        <div class="rounded-2xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $withdrawal->user->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $withdrawal->payment_method }} • {{ $withdrawal->status }}</p>
                                </div>
                                <p class="text-sm font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $withdrawal->amount, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <a href="{{ route('admin.plans') }}" class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm uppercase tracking-[0.25em] text-zinc-400">CRUD</p>
                <p class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">Plan Management</p>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Create, edit, and delete package rules.</p>
            </a>
            <a href="{{ route('admin.withdrawals.export') }}" class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm uppercase tracking-[0.25em] text-zinc-400">Export</p>
                <p class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">Payout CSV</p>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Download the latest withdrawal queue.</p>
            </a>
            <a href="{{ route('admin.reports') }}" class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm uppercase tracking-[0.25em] text-zinc-400">Insights</p>
                <p class="mt-2 text-xl font-semibold text-zinc-950 dark:text-white">Binary Reports</p>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Carry forward, bonus, and revenue performance.</p>
            </a>
        </section>
    </div>
</x-layouts::app>
