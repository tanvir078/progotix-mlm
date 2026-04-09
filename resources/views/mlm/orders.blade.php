<x-layouts::app :title="__('Orders')">
    @php
        $orderStats = [
            [
                'label' => 'Orders',
                'value' => number_format((int) $totals['order_count']),
                'meta' => 'Retail records',
                'icon' => 'shopping-cart',
                'tone' => 'brand',
            ],
            [
                'label' => 'Retail Sales',
                'value' => '$'.number_format((float) $totals['retail_sales'], 2),
                'meta' => 'Paid order revenue',
                'icon' => 'currency-dollar',
            ],
            [
                'label' => 'Retail Commission',
                'value' => '$'.number_format((float) $totals['commission'], 2),
                'meta' => 'Personal earnings',
                'icon' => 'banknotes',
                'tone' => 'accent',
            ],
            [
                'label' => 'Team Sales Bonus',
                'value' => '$'.number_format((float) $totals['team_sales_bonus'], 2),
                'meta' => 'Sponsor-side bonus',
                'icon' => 'user-group',
            ],
            [
                'label' => 'Total BV',
                'value' => number_format((float) $totals['total_bv'], 2),
                'meta' => 'Volume generated',
                'icon' => 'arrow-trending-up',
            ],
        ];
    @endphp

    <x-app-page spacing="loose">
        @if (session('status'))
            <div class="app-alert app-alert-success">
                {{ session('status') }}
            </div>
        @endif

        <x-app-panel variant="hero" padding="hero">
            <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr] xl:items-end">
                <div class="space-y-4">
                    <p class="app-kicker">Retail Order Desk</p>
                    <div class="space-y-4">
                        <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-white sm:text-4xl xl:text-[2.6rem]">
                            Clean order history with line-item visibility, commission summary, and volume signals for day-to-day follow-up.
                        </h1>
                        <p class="max-w-2xl text-sm leading-7 text-white/82 sm:text-base">
                            এই screen-এ আপনার retail sales performance, item-level order records, আর income-ready totals একসাথে দেখা যাবে।
                            Mobile-এও দ্রুত scroll করে important order data ধরার জন্য layout compact রাখা হয়েছে।
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/62">Average Order Value</p>
                        <p class="mt-3 text-3xl font-semibold text-white">${{ number_format((float) $totals['average_order_value'], 2) }}</p>
                        <p class="mt-2 text-sm text-white/72">Per retail order revenue benchmark.</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/58">Next Move</p>
                        <a href="{{ route('mlm.shop') }}" class="mt-3 inline-flex text-lg font-semibold text-white" wire:navigate>
                            Open shop
                        </a>
                        <p class="mt-2 text-sm leading-6 text-white/72">
                            New product order place করলে এই history section auto-grow করবে।
                        </p>
                    </div>
                </div>
            </div>
        </x-app-panel>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ($orderStats as $stat)
                <x-app-stat-card
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :meta="$stat['meta']"
                    :icon="$stat['icon']"
                    :tone="$stat['tone'] ?? 'default'"
                />
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <x-app-panel>
                <x-app-section-heading
                    title="Retail Order History"
                    description="Order record, line items, commissionable volume, and paid status in one continuous ledger."
                    eyebrow="History"
                >
                    <x-slot:actions>
                        <a href="{{ route('mlm.shop') }}" class="app-inline-link" wire:navigate>Open shop</a>
                    </x-slot:actions>
                </x-app-section-heading>

                <div class="mt-6 app-list-stack">
                    @forelse ($orders as $order)
                        @php
                            $statusClass = match ($order->status) {
                                'paid' => 'app-status-badge app-status-badge-success',
                                'pending' => 'app-status-badge app-status-badge-warning',
                                default => 'app-status-badge app-status-badge-neutral',
                            };
                            $itemCount = (int) $order->items->sum('quantity');
                        @endphp

                        <article class="app-panel !p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="app-kicker">{{ $order->order_no }}</p>
                                        <span class="{{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                                    </div>
                                    <h2 class="text-xl font-semibold text-zinc-950 dark:text-white">Retail Order</h2>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Placed {{ $order->placed_at?->format('d M Y, h:i A') ?? 'Not set' }}
                                    </p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                    <div class="app-metric-tile">
                                        <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Subtotal</p>
                                        <p class="mt-2 font-semibold text-zinc-950 dark:text-white">${{ number_format((float) $order->subtotal, 2) }}</p>
                                    </div>
                                    <div class="app-metric-tile">
                                        <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Items</p>
                                        <p class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ number_format($itemCount) }}</p>
                                    </div>
                                    <div class="app-metric-tile">
                                        <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Commission</p>
                                        <p class="mt-2 font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format((float) $order->commission_amount, 2) }}</p>
                                    </div>
                                    <div class="app-metric-tile">
                                        <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">BV</p>
                                        <p class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $order->total_bv, 2) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 md:grid-cols-2">
                                @foreach ($order->items as $item)
                                    <div class="app-list-row">
                                        <div class="space-y-1">
                                            <p class="font-semibold text-zinc-950 dark:text-white">{{ $item->product_name }}</p>
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $item->sku }} • Qty {{ $item->quantity }}</p>
                                        </div>

                                        <div class="text-left sm:text-right">
                                            <p class="font-semibold text-zinc-950 dark:text-white">${{ number_format((float) $item->line_total, 2) }}</p>
                                            <p class="mt-1 text-xs uppercase tracking-[0.22em] text-zinc-400">
                                                BV {{ number_format((float) $item->line_bv, 2) }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @empty
                        <div class="app-empty-state">
                            No retail orders yet. Shop থেকে order place করলেই আপনার sales history, commission, আর BV এখানে দেখা যাবে।
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">{{ $orders->links() }}</div>
            </x-app-panel>

            <div class="grid gap-6">
                <x-app-panel variant="contrast">
                    <x-app-section-heading
                        title="Order Performance Guide"
                        description="A quick read on what these totals mean for retail-led MLM growth."
                        eyebrow="Insights"
                    />

                    <div class="mt-5 app-list-stack">
                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Retail sales create real transaction proof</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    Recruitment ছাড়াও sales activity দেখাতে order history সবচেয়ে strong operating signal।
                                </p>
                            </div>
                        </div>
                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Commission and volume should stay traceable</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    প্রতিটা order-এর সাথে subtotal, commission, item count, আর BV দেখা থাকলে audit সহজ হয়।
                                </p>
                            </div>
                        </div>
                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Average order value helps product strategy</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    কোন bundle বা price point বেশি কাজ করছে সেটা ধরতে average order value useful।
                                </p>
                            </div>
                        </div>
                    </div>
                </x-app-panel>

                <x-app-panel>
                    <x-app-section-heading
                        title="Next Actions"
                        description="Keep your order pipeline and earnings flywheel moving."
                        eyebrow="Action"
                    />

                    <div class="mt-5 app-list-stack">
                        <a href="{{ route('mlm.shop') }}" class="app-list-row transition hover:border-teal-300" wire:navigate>
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Place a new retail order</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    নতুন product order দিয়ে retail sales এবং volume বাড়ান।
                                </p>
                            </div>
                            <flux:icon name="arrow-right" class="size-5 text-zinc-400" />
                        </a>

                        <a href="{{ route('mlm.earnings') }}" class="app-list-row transition hover:border-teal-300" wire:navigate>
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Review earnings ledger</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    Order-based commission postings earning screen-এ যাচাই করুন।
                                </p>
                            </div>
                            <flux:icon name="arrow-right" class="size-5 text-zinc-400" />
                        </a>
                    </div>
                </x-app-panel>
            </div>
        </section>
    </x-app-page>
</x-layouts::app>
