@props([
    'node' => null,
    'label' => null,
])

@if ($node)
    <div class="flex flex-col items-center gap-4">
        <div class="w-full max-w-xs rounded-2xl border border-zinc-200 bg-white p-4 text-center shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            @if ($label)
                <p class="text-xs uppercase tracking-[0.25em] text-zinc-400">{{ $label }}</p>
            @endif
            <p class="mt-2 text-base font-semibold text-zinc-950 dark:text-white">{{ $node['member']->name }}</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ '@'.$node['member']->username }} • {{ $node['member']->member_code }}</p>
            <p class="mt-3 text-xs uppercase tracking-[0.2em] text-zinc-400">Wallet</p>
            <p class="mt-1 text-sm font-medium text-emerald-600 dark:text-emerald-400">৳{{ number_format((float) $node['member']->balance, 2) }}</p>
        </div>

        @if ($node['left'] || $node['right'])
            <div class="grid w-full gap-4 md:grid-cols-2">
                <x-binary-tree-node :node="$node['left']" label="Left" />
                <x-binary-tree-node :node="$node['right']" label="Right" />
            </div>
        @endif
    </div>
@else
    <div class="w-full max-w-xs rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-4 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/40 dark:text-zinc-400">
        @if ($label)
            <p class="text-xs uppercase tracking-[0.25em] text-zinc-400">{{ $label }}</p>
        @endif
        <p class="mt-2">Empty slot</p>
    </div>
@endif
