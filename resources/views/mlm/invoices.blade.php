<x-layouts::app :title="__('Invoices')">
    <div class="flex flex-col gap-6">
        <section class="grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Paid invoices total</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $summary['paid_total'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Invoice count</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">{{ $summary['invoice_count'] }}</p>
            </div>
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Package invoices</h1>
            <div class="mt-5 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-left text-zinc-500 dark:bg-zinc-800/70 dark:text-zinc-400">
                        <tr>
                            <th class="px-4 py-3 font-medium">Invoice</th>
                            <th class="px-4 py-3 font-medium">Package</th>
                            <th class="px-4 py-3 font-medium">Issued</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td class="px-4 py-4">
                                    <p class="font-medium text-zinc-950 dark:text-white">{{ $invoice->invoice_no }}</p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $invoice->title }}</p>
                                    <a href="{{ route('mlm.invoices.pdf', $invoice) }}" class="mt-2 inline-block text-xs font-medium text-teal-600 dark:text-teal-400">
                                        Download PDF
                                    </a>
                                </td>
                                <td class="px-4 py-4 text-zinc-500 dark:text-zinc-400">{{ $invoice->subscription?->plan?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-4 text-zinc-500 dark:text-zinc-400">{{ $invoice->issued_at->format('d M Y, h:i A') }}</td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium uppercase tracking-[0.2em] text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $invoice->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $invoice->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">এখনো কোনো invoice নেই।</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $invoices->links() }}</div>
        </section>
    </div>
</x-layouts::app>
