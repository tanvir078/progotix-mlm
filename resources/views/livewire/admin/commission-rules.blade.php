<div class="space-y-8">
    <div class="space-y-4">
        <flux:heading size="lg">Commission Rule Editor</flux:heading>
        <flux:text size="sm" class="text-zinc-500">Edit subscription levels, retail distribution, binary rates, and refund policy. Changes apply immediately after save.</flux:text>

        @if (session('message'))
            <flux:alert variant="success">{{ session('message') }}</flux:alert>
        @endif
    </div>

    {{-- Subscription Level Distribution --}}
    <flux:card>
        <flux:card.heading>Subscription Level Distribution</flux:card.heading>
        <flux:card.content>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Level</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Ratio (%)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($subscriptionLevels as $index => $level)
                            <tr>
                                <td class="px-4 py-4">
                                    <flux:input type="number" wire:model="subscriptionLevels.{{ $index }}.level" class="w-20" min="1" />
                                </td>
                                <td class="px-4 py-4">
                                    <flux:input type="number" step="0.01" wire:model="subscriptionLevels.{{ $index }}.ratio" class="w-24" min="0" max="1" />
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <flux:button size="sm" variant="destructive-outline" wire:click="removeSubscriptionLevel({{ $index }})" icon="trash">
                                        Remove
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <flux:button wire:click="addSubscriptionLevel" size="sm" class="mt-4">
                Add Level
            </flux:button>
        </flux:card.content>
    </flux:card>

    {{-- Retail Team Distribution --}}
    <flux:card>
        <flux:card.heading>Retail Team Distribution</flux:card.heading>
        <flux:card.content>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Level</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Ratio (%)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($retailTeamDist as $index => $dist)
                            <tr>
                                <td class="px-4 py-4">
                                    <flux:input type="number" wire:model="retailTeamDist.{{ $index }}.level" class="w-20" min="1" />
                                </td>
                                <td class="px-4 py-4">
                                    <flux:input type="number" step="0.01" wire:model="retailTeamDist.{{ $index }}.ratio" class="w-24" min="0" max="1" />
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <flux:button size="sm" variant="destructive-outline" wire:click="removeRetailDist({{ $index }})" icon="trash">
                                        Remove
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <flux:button wire:click="addRetailDist" size="sm" class="mt-4">
                Add Distribution Level
            </flux:button>
        </flux:card.content>
    </flux:card>

    {{-- Binary Rates --}}
    <flux:card>
        <flux:card.heading>Binary Bonus Rates</flux:card.heading>
        <flux:card.content>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <flux:label for="left_right">Left/Right Pair Rate</flux:label>
                    <flux:input id="left_right" type="number" step="0.01" wire:model="binaryRates.left_right" />
                </div>
                <div>
                    <flux:label for="matching_bonus">Matching Bonus</flux:label>
                    <flux:input id="matching_bonus" type="number" step="0.01" wire:model="binaryRates.matching_bonus" />
                </div>
            </div>
        </flux:card.content>
    </flux:card>

    {{-- Refund Policy --}}
    <flux:card>
        <flux:card.heading>Refund Policy</flux:card.heading>
        <flux:card.content>
            <flux:textarea rows="4" wire:model="refundPolicy" placeholder="Enter refund policy rules..."></flux:textarea>
        </flux:card.content>
    </flux:card>

    <flux:button variant="primary" wire:click="save" size="lg" class="w-full sm:w-auto">
        Save & Apply Rules
    </flux:button>
</div>

