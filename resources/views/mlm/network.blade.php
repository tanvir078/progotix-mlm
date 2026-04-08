<x-layouts::app :title="__('Referral Network')">
    <div class="flex flex-col gap-6">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-zinc-400">Network Overview</p>
                    <h1 class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">Referral Tree</h1>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        Sponsor: {{ $user->referrer?->name ?? 'No sponsor' }} • Referral username: {{ '@'.$user->username }}
                    </p>
                </div>

                <div class="rounded-2xl bg-zinc-50 p-4 text-sm text-zinc-600 dark:bg-zinc-800/60 dark:text-zinc-300">
                    <p class="font-medium text-zinc-950 dark:text-white">Share this link</p>
                    <p class="mt-2 break-all">{{ $user->referral_link }}</p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Direct Members</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($directReferrals as $member)
                        <div class="rounded-2xl border border-zinc-200 p-5 dark:border-zinc-700">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <p class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $member->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ '@'.$member->username }} • {{ $member->member_code }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4 text-sm md:text-right">
                                    <div>
                                        <p class="text-zinc-400">Joined</p>
                                        <p class="mt-1 font-medium text-zinc-700 dark:text-zinc-200">{{ $member->created_at->format('d M Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-zinc-400">Downline</p>
                                        <p class="mt-1 font-medium text-zinc-700 dark:text-zinc-200">{{ $member->referrals_count }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            কোনো direct member নেই।
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Second Level Snapshot</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">আপনার direct members-এর নিচের সদস্যরা</p>

                <div class="mt-5 space-y-3">
                    @forelse ($secondLevelMembers as $member)
                        <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/60">
                            <p class="font-medium text-zinc-950 dark:text-white">{{ $member->name }}</p>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ '@'.$member->username }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            এখনো second level network তৈরি হয়নি।
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
