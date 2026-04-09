<x-layouts::app :title="__('Ranks')">
    @php
        $rankCards = [
            [
                'label' => 'Current Rank',
                'value' => $rankStats['current_rank'],
                'meta' => 'Present level',
                'icon' => 'trophy',
                'tone' => 'brand',
            ],
            [
                'label' => 'Direct Referrals',
                'value' => number_format((float) $metrics['direct_referrals'], 0),
                'meta' => 'Current count',
                'icon' => 'user-group',
            ],
            [
                'label' => 'Personal Sales',
                'value' => '$'.number_format((float) $metrics['personal_sales'], 2),
                'meta' => 'Retail volume',
                'icon' => 'shopping-bag',
                'tone' => 'accent',
            ],
            [
                'label' => 'Reward Total',
                'value' => '$'.number_format((float) $rankStats['reward_total'], 2),
                'meta' => 'Career rewards',
                'icon' => 'gift',
            ],
        ];
    @endphp

    <x-app-page spacing="loose">
        <x-app-panel variant="hero" padding="hero">
            <div class="grid gap-6 xl:grid-cols-[1.12fr_0.88fr] xl:items-end">
                <div class="space-y-4">
                    <p class="app-kicker">Rank & Rewards Engine</p>
                    <div class="space-y-4">
                        <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-white sm:text-4xl xl:text-[2.6rem]">
                            Visible rank ladder with qualification progress, next target clarity, and achievement history in one screen.
                        </h1>
                        <p class="max-w-2xl text-sm leading-7 text-white/82 sm:text-base">
                            Direct referrals, personal sales, আর team volume কতটা complete হয়েছে তা এখন clearer progress structure-এ দেখানো হচ্ছে।
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/62">Current Level</p>
                        <p class="mt-3 text-3xl font-semibold text-white">{{ $currentRank?->name ?? 'Starter' }}</p>
                        <p class="mt-2 text-sm text-white/72">
                            {{ $nextRank ? 'Next: '.$nextRank->name : 'Top rank reached' }}
                        </p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/58">Unlocked Ranks</p>
                        <p class="mt-3 text-3xl font-semibold text-white">{{ number_format((int) $rankStats['unlocked_count']) }}</p>
                        <p class="mt-2 text-sm text-white/72">Achievement records: {{ number_format((int) $rankStats['achievement_count']) }}</p>
                    </div>
                </div>
            </div>
        </x-app-panel>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($rankCards as $card)
                <x-app-stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :meta="$card['meta']"
                    :icon="$card['icon']"
                    :tone="$card['tone'] ?? 'default'"
                />
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.94fr_1.06fr]">
            <div class="grid gap-6">
                <x-app-panel variant="contrast">
                    <x-app-section-heading
                        title="Next Rank Progress"
                        description="{{ $nextRank ? 'Progress toward '.$nextRank->name : 'You have already unlocked the highest configured rank.' }}"
                        eyebrow="Progress"
                    />

                    @if ($nextRank)
                        <div class="mt-5 space-y-4">
                            @foreach ($progress as $item)
                                <div class="rounded-[1.35rem] border border-zinc-200/80 p-4 dark:border-zinc-700">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="font-semibold text-zinc-950 dark:text-white">{{ $item['label'] }}</p>
                                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ number_format((float) $item['current'], 2) }}{{ $item['suffix'] }} / {{ number_format((float) $item['target'], 2) }}{{ $item['suffix'] }}
                                            </p>
                                        </div>
                                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-300">
                                            Remaining {{ number_format((float) $item['remaining'], 2) }}{{ $item['suffix'] }}
                                        </p>
                                    </div>
                                    <div class="app-progress-track mt-4">
                                        <div class="app-progress-bar" style="width: {{ $item['percentage'] }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="app-empty-state mt-5">
                            Current configuration অনুযায়ী আপনি already highest qualifying rank-এ আছেন।
                        </div>
                    @endif
                </x-app-panel>

                <x-app-panel>
                    <x-app-section-heading
                        title="Rank Celebration History"
                        description="Career milestones and unlocked reward history."
                        eyebrow="Achievements"
                    />

                    <div class="mt-5 app-list-stack">
                        @forelse ($achievements as $achievement)
                            <div class="app-list-row">
                                <div class="space-y-1">
                                    <p class="font-semibold text-zinc-950 dark:text-white">{{ $achievement->rank->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Unlocked {{ $achievement->achieved_at?->format('d M Y') }}</p>
                                </div>
                                <p class="font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format((float) $achievement->bonus_amount, 2) }} reward</p>
                            </div>
                        @empty
                            <div class="app-empty-state">
                                No rank upgrades yet. Start with shop orders and team growth.
                            </div>
                        @endforelse
                    </div>
                </x-app-panel>
            </div>

            <x-app-panel>
                <x-app-section-heading
                    title="Rank Ladder"
                    description="Full ladder with requirements, reward size, and unlocked state."
                    eyebrow="Ladder"
                />

                <div class="mt-6 app-list-stack">
                    @foreach ($ranks as $rank)
                        @php
                            $unlocked = $currentRank && $rank->sort_order <= $currentRank->sort_order;
                        @endphp
                        <div class="rounded-[1.5rem] border p-5 {{ $unlocked ? 'border-emerald-300 bg-emerald-50/70 dark:border-emerald-900 dark:bg-emerald-950/20' : 'border-zinc-200 dark:border-zinc-700' }}">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="app-kicker">{{ $rank->slug }}</p>
                                        <span class="{{ $unlocked ? 'app-status-badge app-status-badge-success' : 'app-status-badge app-status-badge-neutral' }}">
                                            {{ $unlocked ? 'Unlocked' : 'Locked' }}
                                        </span>
                                    </div>
                                    <h3 class="text-xl font-semibold text-zinc-950 dark:text-white">{{ $rank->name }}</h3>
                                    <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                        Requires {{ $rank->direct_referrals_required }} direct referrals,
                                        ${{ number_format((float) $rank->personal_sales_required, 2) }} personal sales,
                                        and {{ number_format((float) $rank->team_volume_required, 2) }} team volume.
                                    </p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                                    <div class="app-metric-tile">
                                        <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Reward</p>
                                        <p class="mt-2 font-semibold text-zinc-950 dark:text-white">${{ number_format((float) $rank->bonus_amount, 2) }}</p>
                                    </div>
                                    <div class="app-metric-tile">
                                        <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Sort Order</p>
                                        <p class="mt-2 font-semibold text-zinc-950 dark:text-white">{{ number_format((int) $rank->sort_order) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-app-panel>
        </section>
    </x-app-page>
</x-layouts::app>
