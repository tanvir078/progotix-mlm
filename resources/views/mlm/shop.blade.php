<x-layouts::app :title="__('Retail Shop')">
    @php
        $shopStats = [
            [
                'label' => 'Active SKUs',
                'value' => number_format((int) $productStats['active_skus']),
                'meta' => 'Live catalog',
                'icon' => 'shopping-bag',
                'tone' => 'brand',
            ],
            [
                'label' => 'Categories',
                'value' => number_format((int) $productStats['category_count']),
                'meta' => 'Merchandise groups',
                'icon' => 'squares-plus',
            ],
            [
                'label' => 'Top Retail Rate',
                'value' => number_format((float) $productStats['top_commission_rate'], 2).'%',
                'meta' => 'Highest product margin',
                'icon' => 'banknotes',
                'tone' => 'accent',
            ],
            [
                'label' => 'Top BV',
                'value' => number_format((float) $productStats['top_bv'], 2),
                'meta' => 'Highest volume SKU',
                'icon' => 'arrow-trending-up',
            ],
        ];
    @endphp

    <x-app-page spacing="loose">
        @if ($errors->any())
            <div class="app-alert app-alert-danger">
                <p class="font-medium">Order request review করুন।</p>
                <p class="mt-1 text-sm">{{ $errors->first() }}</p>
            </div>
        @endif

        <x-app-panel variant="hero" padding="hero">
            <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr] xl:items-end">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <p class="app-kicker">Retail Commerce Engine</p>
                        <div class="space-y-4">
                            <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-white sm:text-4xl xl:text-[2.7rem]">
                                Mobile-ready product selling surface with commission, BV, and order triggers aligned in one workflow.
                            </h1>
                            <p class="max-w-2xl text-sm leading-7 text-white/82 sm:text-base">
                                এই module recruitment-only MLM pattern থেকে বের হয়ে real e-commerce driven growth build করার জন্য designed.
                                Product, retail commission, team bonus trigger, আর volume visibility সব এক flow-এ রাখা হয়েছে।
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @foreach ($categories->take(4) as $category)
                            <span class="app-pill app-pill-primary">
                                {{ $category['label'] }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/62">Recommended Model</p>
                        <p class="mt-3 text-xl font-semibold text-white">{{ $strategy['recommended_model'] }}</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/58">Commerce Rules</p>
                        <div class="mt-4 space-y-3">
                            @foreach ($rules as $rule)
                                <div class="flex items-start gap-3 text-sm leading-6 text-white/80">
                                    <span class="mt-1 inline-flex size-6 items-center justify-center rounded-full bg-white/10 text-white">
                                        <flux:icon name="check" class="size-4" />
                                    </span>
                                    <span>{{ $rule }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </x-app-panel>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($shopStats as $stat)
                <x-app-stat-card
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :meta="$stat['meta']"
                    :icon="$stat['icon']"
                    :tone="$stat['tone'] ?? 'default'"
                />
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.14fr_0.86fr]">
            <x-app-panel>
                <x-app-section-heading
                    title="Featured Retail Products"
                    description="Commission-ready products with price, BV, retail margin, and fast order entry."
                    eyebrow="Catalog"
                >
                    <x-slot:actions>
                        <span class="app-brand-badge">Shop Catalog</span>
                    </x-slot:actions>
                </x-app-section-heading>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @forelse ($products as $product)
                        <article class="app-module-card !p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-3">
                                    <div class="space-y-1">
                                        <p class="app-kicker">{{ $product['category_label'] }}</p>
                                        <h3 class="text-xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $product['name'] }}</h3>
                                    </div>
                                    <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $product['description'] }}</p>
                                </div>

                                <span class="inline-flex rounded-full bg-zinc-950 px-3 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-white dark:bg-white dark:text-zinc-950">
                                    {{ $product['sku'] }}
                                </span>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                <div class="app-metric-tile">
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Price</p>
                                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) $product['price'], 2) }}</p>
                                </div>
                                <div class="app-metric-tile">
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Retail Comm.</p>
                                    <p class="mt-2 text-lg font-semibold text-emerald-600 dark:text-emerald-400">৳{{ number_format((float) $product['commission_amount'], 2) }}</p>
                                </div>
                                <div class="app-metric-tile sm:col-span-2 xl:col-span-1">
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">BV</p>
                                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ number_format((float) $product['team_volume'], 2) }}</p>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-3 text-xs uppercase tracking-[0.22em] text-zinc-400">
                                <span>{{ number_format((float) $product['retail_commission_rate'] * 100, 2) }}% retail</span>
                                <span>{{ number_format((float) $product['team_bonus_rate'] * 100, 2) }}% team bonus</span>
                            </div>

                            <form method="POST" action="{{ route('mlm.orders.store', $product['id']) }}" class="mt-5 space-y-3">
                                @csrf

                                <div class="grid gap-3 sm:grid-cols-[8rem_1fr]">
                                    <div>
                                        <label class="app-form-label">Quantity</label>
                                        <input
                                            name="quantity"
                                            type="number"
                                            min="1"
                                            max="20"
                                            value="1"
                                            class="app-form-control"
                                        />
                                    </div>

                                    <div class="flex items-end">
                                        <button type="submit" class="app-button-primary w-full">
                                            Buy now
                                        </button>
                                    </div>
                                </div>

                                <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    Paid order হলে retail margin, team volume, আর sponsor-side bonus trigger হবে।
                                </p>
                            </form>
                        </article>
                    @empty
                        <div class="app-empty-state md:col-span-2">
                            এখনো কোনো active product নেই। Admin panel থেকে product catalog set up করলে এখানে shop cards দেখা যাবে।
                        </div>
                    @endforelse
                </div>
            </x-app-panel>

            <div class="grid gap-6">
                <x-app-panel variant="contrast">
                    <x-app-section-heading
                        title="Category Snapshot"
                        description="Each retail group with live SKU count and entry economics."
                        eyebrow="Categories"
                    />

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($categories as $category)
                            <div class="app-metric-tile">
                                <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">{{ $category['key'] }}</p>
                                <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $category['label'] }}</p>
                                <div class="mt-3 space-y-1 text-sm text-zinc-500 dark:text-zinc-400">
                                    <p>{{ $category['product_count'] }} live product{{ $category['product_count'] === 1 ? '' : 's' }}</p>
                                    <p>
                                        Starts at
                                        {{ $category['starting_price'] !== null ? '৳'.number_format((float) $category['starting_price'], 2) : 'not set' }}
                                    </p>
                                    <p>Top BV {{ $category['top_bv'] !== null ? number_format((float) $category['top_bv'], 2) : '0.00' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-app-panel>

                <x-app-panel>
                    <x-app-section-heading
                        title="Why This MLM Style Is Better"
                        description="Safer operating principles for a commerce-led hybrid MLM."
                        eyebrow="Better Direction"
                    />

                    <div class="mt-5 app-list-stack">
                        @foreach ($strategy['principles'] as $principle)
                            <div class="app-list-row">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 inline-flex size-6 items-center justify-center rounded-full bg-teal-500/12 text-teal-700 dark:text-teal-300">
                                        <flux:icon name="check" class="size-4" />
                                    </span>
                                    <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $principle }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-app-panel>

                <x-app-panel>
                    <x-app-section-heading
                        title="Avoid As Core Product Strategy"
                        description="High-risk directions that should stay out of the main commerce engine."
                        eyebrow="Risk Guard"
                    />

                    <div class="mt-5 app-list-stack">
                        @foreach ($strategy['avoid'] as $item)
                            <div class="app-list-row !border-rose-200/70 dark:!border-rose-900/40">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 inline-flex size-6 items-center justify-center rounded-full bg-rose-500/12 text-rose-700 dark:text-rose-300">
                                        <flux:icon name="x-mark" class="size-4" />
                                    </span>
                                    <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $item }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-app-panel>
            </div>
        </section>
    </x-app-page>
</x-layouts::app>
