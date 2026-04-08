<x-layouts::app :title="__('Members')">
    <div class="flex flex-col gap-6">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold text-zinc-950 dark:text-white">Member Directory</h1>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Search members and update their profile, sponsor, wallet, or admin access.</p>
                </div>
                <form method="GET" class="flex w-full max-w-md gap-3">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search name, username, email, member code" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-hidden transition focus:border-teal-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white" />
                    <button type="submit" class="rounded-2xl bg-zinc-950 px-5 py-3 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">Search</button>
                </form>
            </div>
        </section>

        <section class="space-y-4">
            @forelse ($members as $member)
                <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                        <div>
                            <p class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $member->name }}</p>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ '@'.$member->username }} • {{ $member->member_code }} • {{ $member->subscriptions->first()?->plan?->name ?? 'No plan' }}
                            </p>

                            <div class="mt-4 grid gap-3 md:grid-cols-3">
                                <div class="rounded-2xl bg-zinc-50 p-4 text-sm dark:bg-zinc-800/60">
                                    <p class="text-zinc-400">Sponsor</p>
                                    <p class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $member->referrer?->username ?? 'N/A' }}</p>
                                </div>
                                <div class="rounded-2xl bg-zinc-50 p-4 text-sm dark:bg-zinc-800/60">
                                    <p class="text-zinc-400">Binary Parent</p>
                                    <p class="mt-1 font-medium text-zinc-950 dark:text-white">
                                        {{ $member->binaryParent?->username ?? 'Root' }}
                                        @if ($member->binary_position)
                                            <span class="text-xs uppercase tracking-[0.2em]">({{ $member->binary_position }})</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="rounded-2xl bg-zinc-50 p-4 text-sm dark:bg-zinc-800/60">
                                    <p class="text-zinc-400">Wallet</p>
                                    <p class="mt-1 font-medium text-zinc-950 dark:text-white">৳{{ number_format((float) $member->balance, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <form method="POST" action="{{ route('admin.members.update', $member) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <input name="name" value="{{ $member->name }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                                <input name="username" value="{{ $member->username }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                                <input name="email" value="{{ $member->email }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                                <input name="balance" type="number" step="0.01" value="{{ $member->balance }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950" />
                                <select name="referrer_id" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                                    <option value="">No sponsor</option>
                                    @foreach ($referrers as $referrer)
                                        @if ($referrer->id !== $member->id)
                                            <option value="{{ $referrer->id }}" @selected($member->referrer_id === $referrer->id)>{{ $referrer->name }} ({{ '@'.$referrer->username }})</option>
                                        @endif
                                    @endforeach
                                </select>
                                <label class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    <input type="checkbox" name="is_admin" value="1" @checked($member->is_admin) />
                                    Admin access
                                </label>
                                <button type="submit" class="rounded-2xl bg-zinc-950 px-4 py-3 text-sm font-medium text-white dark:bg-white dark:text-zinc-950">
                                    Save changes
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.members.destroy', $member) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-2xl bg-rose-600 px-4 py-3 text-sm font-medium text-white">
                                    Delete member
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-dashed border-zinc-300 p-6 text-center text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                    No members found.
                </div>
            @endforelse
            <div>{{ $members->links() }}</div>
        </section>
    </div>
</x-layouts::app>
