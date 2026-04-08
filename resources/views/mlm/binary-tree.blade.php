<x-layouts::app :title="__('Binary Tree')">
    <div class="flex flex-col gap-6">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-zinc-400">Binary Placement</p>
                    <h1 class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">Binary Tree View</h1>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        Parent: {{ $user->binaryParent?->name ?? 'Root position' }} • Position: {{ $user->binary_position ?? 'root' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-zinc-50 p-4 text-sm dark:bg-zinc-800/60">
                    <p class="text-zinc-500 dark:text-zinc-400">Binary team size</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ $user->binaryTeamCount() }}</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Left carry</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) ($user->binaryLedger?->left_carry ?? 0), 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Right carry</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) ($user->binaryLedger?->right_carry ?? 0), 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pair volume</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) ($user->binaryLedger?->pair_volume ?? 0), 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Binary bonus earned</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950 dark:text-white">৳{{ number_format((float) ($user->binaryLedger?->total_binary_bonus ?? 0), 2) }}</p>
            </div>
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <div class="min-w-[700px]">
                    <x-binary-tree-node :node="$tree" label="You" />
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
