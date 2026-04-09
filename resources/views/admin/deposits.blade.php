<x-layouts::app :title="__('Deposit Queue')">
    <x-app-page spacing="relaxed">
        @if (session('status'))
            <div class="app-alert app-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="app-alert app-alert-danger">
                <p class="font-medium">Deposit queue review করুন।</p>
                <p class="mt-1 text-sm">{{ $errors->first() }}</p>
            </div>
        @endif

        <x-app-panel variant="hero" padding="hero">
            <div class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr] xl:items-end">
                <div class="space-y-4">
                    <p class="app-kicker">Admin Deposit Queue</p>
                    <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                        Review incoming payment proofs and credit wallets only after approval.
                    </h1>
                    <p class="max-w-2xl text-sm leading-7 text-white/82 sm:text-base">
                        Manual review flow-এর মাধ্যমে e-wallet, bank, card, আর crypto funding request centrally process করুন।
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/62">Pending</p>
                        <p class="mt-3 text-3xl font-semibold text-white">${{ number_format((float) $stats['pending_total'], 2) }}</p>
                        <p class="mt-2 text-sm text-white/72">{{ number_format((int) $stats['pending_count']) }} requests awaiting action.</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/58">Approved Credit</p>
                        <p class="mt-3 text-3xl font-semibold text-white">${{ number_format((float) $stats['approved_total'], 2) }}</p>
                        <p class="mt-2 text-sm text-white/72">Proof attached requests: {{ number_format((int) $stats['proof_count']) }}</p>
                    </div>
                </div>
            </div>
        </x-app-panel>

        <x-app-panel>
            <form method="GET" class="grid gap-4 md:grid-cols-[1.3fr_0.7fr_0.7fr]">
                <div>
                    <label class="app-form-label">Search</label>
                    <input name="search" value="{{ $search }}" class="app-form-control" placeholder="Reference, member, or method" />
                </div>
                <div>
                    <label class="app-form-label">Status</label>
                    <select name="status" class="app-form-control">
                        @foreach (['pending', 'approved', 'rejected', 'all'] as $filter)
                            <option value="{{ $filter }}" @selected($status === $filter)>{{ ucfirst($filter) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="app-form-label">Type</label>
                    <select name="type" class="app-form-control">
                        <option value="">All types</option>
                        @foreach ($types as $paymentType)
                            <option value="{{ $paymentType }}" @selected($type === $paymentType)>{{ ucfirst(str_replace('_', ' ', $paymentType)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="app-button-primary md:col-span-3">Apply filters</button>
            </form>
        </x-app-panel>

        <section class="app-list-stack">
            @forelse ($requests as $deposit)
                @php
                    $statusClass = match ($deposit->status) {
                        'approved' => 'app-status-badge app-status-badge-success',
                        'rejected' => 'app-status-badge app-status-badge-danger',
                        default => 'app-status-badge app-status-badge-warning',
                    };
                @endphp

                <x-app-panel>
                    <div class="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $deposit->user->name }}</p>
                                <span class="{{ $statusClass }}">{{ $deposit->status }}</span>
                            </div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ '@'.$deposit->user->username }} • {{ $deposit->payment_method_name }} • {{ ucfirst(str_replace('_', ' ', $deposit->payment_method_type)) }}
                            </p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Ref: {{ $deposit->transaction_reference ?: 'Not provided' }} • Submitted {{ $deposit->submitted_at?->format('d M Y, h:i A') ?? $deposit->created_at->format('d M Y, h:i A') }}
                            </p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Gross {{ number_format((float) $deposit->amount, 2) }} {{ $deposit->currency }} • Charge {{ number_format((float) $deposit->charge_amount, 2) }} • Net {{ number_format((float) $deposit->net_amount, 2) }}
                            </p>
                            @if ($deposit->sender_name || $deposit->sender_account)
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                    Sender: {{ $deposit->sender_name ?: 'Not set' }} @if ($deposit->sender_account) • {{ $deposit->sender_account }} @endif
                                </p>
                            @endif
                            @if ($deposit->note)
                                <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">Member note: {{ $deposit->note }}</p>
                            @endif
                            @if ($deposit->admin_note)
                                <p class="text-sm leading-6 text-zinc-600 dark:text-zinc-300">Admin note: {{ $deposit->admin_note }}</p>
                            @endif
                            @if ($deposit->payment_proof_path)
                                <a href="{{ route('admin.deposits.proof', $deposit) }}" class="app-inline-link">Download payment proof</a>
                            @endif
                        </div>

                        <div class="space-y-4">
                            @if ($deposit->status === 'pending')
                                <form method="POST" action="{{ route('admin.deposits.update', $deposit) }}" class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <textarea
                                        name="admin_note"
                                        rows="4"
                                        class="app-form-control app-form-textarea"
                                        placeholder="Optional admin note"
                                    >{{ old('admin_note') }}</textarea>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button name="decision" value="approve" class="app-button-primary">Approve</button>
                                        <button name="decision" value="reject" class="rounded-[1.15rem] bg-rose-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-rose-500">
                                            Reject
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="app-metric-tile">
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Processed</p>
                                    <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">
                                        {{ $deposit->processor?->name ?? 'System' }}
                                    </p>
                                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $deposit->processed_at?->diffForHumans() ?? 'Not available' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-app-panel>
            @empty
                <div class="app-empty-state">
                    কোনো deposit request পাওয়া যায়নি।
                </div>
            @endforelse
        </section>

        <div>{{ $requests->links() }}</div>
    </x-app-page>
</x-layouts::app>
