<x-layouts::app :title="__('Tree Manager')">
    <div class="flex flex-col gap-6">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold text-zinc-950 dark:text-white">Binary Tree Manager</h1>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Selected root: {{ $root->name }} ({{ '@'.$root->username }})</p>
                </div>
                <form method="GET" action="{{ route('admin.binary-tree') }}" class="w-full max-w-sm">
                    <select name="user" onchange="if(this.value){ window.location=this.value }" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-hidden transition focus:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white">
                        <option value="{{ route('admin.binary-tree') }}">Select member root</option>
                        @foreach ($members as $member)
                            <option value="{{ route('admin.binary-tree', $member) }}" @selected($member->id === $root->id)>{{ $member->name }} ({{ '@'.$member->username }})</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <div class="min-w-[900px]">
                    <x-binary-tree-node :node="$tree" label="Root" />
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
