<div class="space-y-6">
    <flux:page.header>
        <flux:heading>Refund & Reversal Requests</flux:heading>
        <flux:text size="sm" class="text-zinc-500">Manage subscription cancellations and order refunds. Approvals auto-reverse commissions and update ledgers.</flux:text>
    </flux:page.header>

    <flux:card>
        <flux:card.content>
            <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between mb-6">
                <flux:radio.group wire:model.live="filterType" class="inline-flex bg-zinc-100 dark:bg-zinc-800 rounded-2xl p-1">
                    <flux:radio value="all" variant="ghost">All</flux:radio>
                    <flux:radio value="subscription" variant="ghost">Subscriptions</flux:radio>
                    <flux:radio value="order" variant="ghost">Orders</flux:radio>
                </flux:radio.group>

                @if(!empty($selected))
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-zinc-500">{{ count($selected) }} selected</span>
                        <flux:button size="sm" wire:click="bulkApprove" variant="destructive" icon="check">
                            Bulk Approve
                        </flux:button>
                    </div>
                @endif
            </div>

            <flux:table>
                <flux:table.head>
                    <flux:table.row>
                        <flux:table.head-cell class="w-12">
                            <flux:checkbox wire:model="selected" value="dummy" />
                        </flux:table.head-cell>
                        <flux:table.head-cell>ID</flux:table.head-cell>
                        <flux:table.head-cell>User</flux:table.head-cell>
                        <flux:table.head-cell>Type</flux:table.head-cell>
                        <flux:table.head-cell>Amount</flux:table.head-cell>
                        <flux:table.head-cell>Status</flux:table.head-cell>
                        <flux:table.head-cell>Requested</flux:table.head-cell>
                        <flux:table.head-cell>Actions</flux:table.head-cell>
                    </flux:table.row>
                </flux:table.head>

                <flux:table.body>
                    @foreach($refunds as $refund)
                        @php
                            $id = $refund->id;
                            $type = $refund instanceof \App\Models\MlmSubscription ? 'subscription' : 'order';
                            $user = $refund->user;
                            $amount = $refund->amount ?? $refund->subtotal;
                            $status = ucfirst($refund->status ?? 'Pending');
                            $item = $refund->plan->name ?? ($refund->items->first()->product_name ?? 'N/A');
                        @endphp
                        <flux:table.row wire:key="{{ $type }}-{{ $id }}">
                            <flux:table.cell>
                                <flux:checkbox wire:model="selected" :value="$type.{{ $id }}" />
                            </flux:table.cell>
                            <flux:table.cell>#{{ $id }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="font-medium">{{ $user->name }}</div>
                                <div class="text-sm text-zinc-500">{{ $user->email }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ ucfirst($type) }}
                                <div class="text-xs text-zinc-500 font-mono">{{ $item }}</div>
                            </flux:table.cell>
                            <flux:table.cell>${{ number_format($amount, 2) }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge variant="{{ $status === 'Refunded' ? 'success' : ($status === 'Paid' ? 'warning' : 'destructive') }}">
                                    {{ $status }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $refund->updated_at?->format('M d') ?? 'N/A' }}</flux:table.cell>
                            <flux:table.cell class="text-right">
                                <flux:button size="sm" variant="destructive-outline" wire:click="approve('{{ $id }}', '{{ $type }}')" icon="check">
                                    Approve & Reverse
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.body>
            </flux:table>

            {{ $refunds->links() }}
        </flux:card.content>
    </flux:card>
</div>

