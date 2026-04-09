<x-layouts::app :title="__('Withdrawal Queue')">
    <div class="flex flex-col gap-6">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    @foreach (['pending', 'approved', 'rejected', 'all'] as $filter)
                        <a href="{{ route('admin.withdrawals', ['status' => $filter]) }}" class="rounded-full px-4 py-2 text-sm font-medium {{ $status === $filter ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300' }}">
                            {{ ucfirst($filter) }}
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('admin.withdrawals.export') }}" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">
                    Download CSV
                </a>
            </div>
        </section>

        <section class="space-y-4">
            @forelse ($requests as $withdrawal)
                <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-2">
                            <p class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $withdrawal->user->name }}</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ '@'.$withdrawal->user->username }} • {{ $withdrawal->payment_method }}</p>
                            @if ($withdrawal->paymentMethod)
                                <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">{{ $withdrawal->paymentMethod->type_label }} • {{ $withdrawal->paymentMethod->country_label }}</p>
                            @endif
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $withdrawal->account_details }}</p>
                            @if ($withdrawal->note)
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">Member note: {{ $withdrawal->note }}</p>
                            @endif
                            @if ($withdrawal->admin_note)
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">Admin note: {{ $withdrawal->admin_note }}</p>
                            @endif
                        </div>

                        <div class="min-w-56 space-y-3">
                            <p class="text-2xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $withdrawal->amount, 2) }}</p>
                            <p class="text-sm uppercase tracking-[0.2em] text-zinc-400">{{ $withdrawal->status }}</p>

                            @if ($withdrawal->status === 'pending')
                                <form method="POST" action="{{ route('admin.withdrawals.update', $withdrawal) }}" class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <textarea name="admin_note" rows="3" placeholder="Optional admin note" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-hidden transition focus:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white"></textarea>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button name="decision" value="approve" class="rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-medium text-white">Approve</button>
                                        <button name="decision" value="reject" class="rounded-2xl bg-rose-600 px-4 py-3 text-sm font-medium text-white">Reject</button>
                                    </div>
                                </form>
                            @else
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Processed {{ $withdrawal->processed_at?->diffForHumans() }} by {{ $withdrawal->processor?->name ?? 'System' }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-dashed border-zinc-300 p-6 text-center text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                    No withdrawal requests found.
                </div>
            @endforelse
        </section>

        <div>{{ $requests->links() }}</div>
    </div>
</x-layouts::app>
