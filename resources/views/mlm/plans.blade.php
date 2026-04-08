<x-layouts::app :title="__('MLM Packages')">
    <div class="flex flex-col gap-6">
        <section class="flex flex-col gap-3">
            <p class="text-sm uppercase tracking-[0.25em] text-zinc-400">Packages</p>
            <h1 class="text-3xl font-semibold text-zinc-950 dark:text-white">MLM package activation</h1>
            <p class="max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">
                Package activate করলে আপনার account-এ subscription যুক্ত হবে, আর sponsor থাকলে তার wallet-এ direct bonus credit যাবে।
            </p>
        </section>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        @if ($activeSubscription)
            <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Current Active Package</p>
                <div class="mt-2 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $activeSubscription->plan->name }}</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Activated {{ $activeSubscription->started_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <p class="text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $activeSubscription->amount, 2) }}</p>
                </div>
            </section>
        @endif

        <section class="grid gap-5 xl:grid-cols-3">
            @foreach ($plans as $plan)
                <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-[0.25em] text-zinc-400">{{ $plan->code }}</p>
                            <h2 class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ $plan->name }}</h2>
                        </div>
                        <p class="text-2xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $plan->price, 2) }}</p>
                    </div>

                    <p class="mt-4 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $plan->description }}</p>

                    <div class="mt-5 space-y-2 rounded-2xl bg-zinc-50 p-4 text-sm dark:bg-zinc-800/60">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">Direct bonus</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">৳{{ number_format((float) $plan->direct_bonus, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400">Level bonus reserve</span>
                            <span class="font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $plan->level_bonus, 2) }}</span>
                        </div>
                    </div>

                    <form action="{{ route('mlm.plans.subscribe', $plan) }}" method="POST" class="mt-5">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-4 py-3 text-sm font-medium text-white transition hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200">
                            Activate package
                        </button>
                    </form>
                </div>
            @endforeach
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Activation History</h2>
            <div class="mt-5 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-left text-zinc-500 dark:bg-zinc-800/70 dark:text-zinc-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Package</th>
                            <th class="px-4 py-3 font-medium">Amount</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Started</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($history as $subscription)
                            <tr>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">{{ $subscription->plan->name }}</td>
                                <td class="px-4 py-3 text-zinc-700 dark:text-zinc-200">৳{{ number_format((float) $subscription->amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium uppercase tracking-[0.2em] text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $subscription->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $subscription->started_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">এখনো কোনো activation history নেই।</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts::app>
