<x-layouts::app :title="__('Plan Management')">
    <div class="flex flex-col gap-6">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h1 class="text-2xl font-semibold text-zinc-950 dark:text-white">Create plan</h1>
                <form method="POST" action="{{ route('admin.plans.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <input name="name" placeholder="Plan name" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                    <input name="code" placeholder="PLAN_CODE" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                    <textarea name="description" rows="3" placeholder="Description" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"></textarea>
                    <div class="grid gap-4 md:grid-cols-2">
                        <input name="price" type="number" step="0.01" placeholder="Price" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                        <input name="sort_order" type="number" step="1" value="0" placeholder="Sort order" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <input name="direct_bonus" type="number" step="0.01" placeholder="Direct bonus" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                        <input name="level_bonus" type="number" step="0.01" placeholder="Level bonus pool" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                    </div>
                    <label class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                        <input type="checkbox" name="is_active" value="1" checked />
                        Active
                    </label>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-4 py-3 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">
                        Create plan
                    </button>
                </form>
            </div>

            <div class="space-y-4">
                @foreach ($plans as $plan)
                    <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            <div class="grid gap-4 md:grid-cols-2">
                                <input name="name" value="{{ $plan->name }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                                <input name="code" value="{{ $plan->code }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                            </div>
                            <textarea name="description" rows="3" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">{{ $plan->description }}</textarea>
                            <div class="grid gap-4 md:grid-cols-4">
                                <input name="price" type="number" step="0.01" value="{{ $plan->price }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                                <input name="direct_bonus" type="number" step="0.01" value="{{ $plan->direct_bonus }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                                <input name="level_bonus" type="number" step="0.01" value="{{ $plan->level_bonus }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                                <input name="sort_order" type="number" step="1" value="{{ $plan->sort_order }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                            </div>
                            <label class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                                <input type="checkbox" name="is_active" value="1" @checked($plan->is_active) />
                                Active
                            </label>
                            <button type="submit" class="rounded-2xl bg-zinc-950 px-4 py-3 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">
                                Update
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-2xl bg-rose-600 px-4 py-3 text-sm font-medium text-white">
                                Delete
                            </button>
                        </form>
                    </div>
                @endforeach
                <div>{{ $plans->links() }}</div>
            </div>
        </section>
    </div>
</x-layouts::app>
