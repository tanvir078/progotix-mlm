<?php

namespace App\Services;

use App\Models\MlmPlan;
use App\Models\MlmSubscription;
use App\Models\MlmTransaction;
use App\Models\User;

class BinaryBonusService
{
    public function __construct(
        private readonly CommissionRuleService $commissionRuleService,
        private readonly CommissionLedgerService $commissionLedgerService,
    ) {}

    public function distribute(User $member, MlmPlan $plan, MlmSubscription $subscription): void
    {
        $current = $member->fresh(['binaryParent']);
        $depth = 1;

        while ($current?->binaryParent) {
            $parent = $current->binaryParent()->first();

            if (! $parent) {
                break;
            }

            $ledger = $parent->binaryLedger()->firstOrCreate([], [
                'left_volume' => 0,
                'right_volume' => 0,
                'left_carry' => 0,
                'right_carry' => 0,
                'pair_volume' => 0,
                'bonus_rate' => $this->commissionRuleService->binaryPairRate(),
                'total_binary_bonus' => 0,
            ]);

            $ledger->bonus_rate = $this->commissionRuleService->binaryPairRate();

            if ($current->binary_position === User::BINARY_LEFT) {
                $ledger->left_volume = (float) $ledger->left_volume + (float) $plan->price;
                $ledger->left_carry = (float) $ledger->left_carry + (float) $plan->price;
            } else {
                $ledger->right_volume = (float) $ledger->right_volume + (float) $plan->price;
                $ledger->right_carry = (float) $ledger->right_carry + (float) $plan->price;
            }

            $pairVolume = min((float) $ledger->left_carry, (float) $ledger->right_carry);

            if ($pairVolume > 0) {
                $bonusAmount = round($pairVolume * (float) $ledger->bonus_rate, 2);

                $ledger->left_carry = (float) $ledger->left_carry - $pairVolume;
                $ledger->right_carry = (float) $ledger->right_carry - $pairVolume;
                $ledger->pair_volume = (float) $ledger->pair_volume + $pairVolume;
                $ledger->total_binary_bonus = (float) $ledger->total_binary_bonus + $bonusAmount;
                $ledger->last_paired_at = now();

                if ($bonusAmount > 0) {
                    $this->commissionLedgerService->credit(
                        $parent,
                        "subscription:{$subscription->id}:binary:earner:{$parent->id}:depth:{$depth}",
                        MlmTransaction::TYPE_BINARY_BONUS,
                        $bonusAmount,
                        'Binary pair bonus',
                        "{$member->name} generated {$pairVolume} pair volume on your {$current->binary_position} leg.",
                        $member,
                        $subscription->id,
                        commissionLevel: $depth,
                    );
                }
            }

            $ledger->save();

            $current = $parent->fresh(['binaryParent']);
            $depth++;
        }
    }

    public function reverse(
        User $member,
        MlmPlan $plan,
        MlmSubscription $subscription,
        ?int $refundRequestId = null,
    ): float {
        $current = $member->fresh(['binaryParent']);
        $depth = 1;
        $reversedAmount = 0.0;

        while ($current?->binaryParent) {
            /** @var User|null $parent */
            $parent = User::query()
                ->lockForUpdate()
                ->find($current->binary_parent_id);

            if (! $parent) {
                break;
            }

            $ledger = $parent->binaryLedger()
                ->lockForUpdate()
                ->first();

            $sourceReference = "subscription:{$subscription->id}:binary:earner:{$parent->id}:depth:{$depth}";

            $sourceTransaction = MlmTransaction::query()
                ->where('reference_key', $sourceReference)
                ->first();

            $pairVolume = $this->pairVolumeFromSourceTransaction($sourceTransaction);
            $unmatchedVolume = max(round((float) $plan->price - $pairVolume, 2), 0);

            if ($ledger) {
                if ($current->binary_position === User::BINARY_LEFT) {
                    $ledger->left_volume = max((float) $ledger->left_volume - (float) $plan->price, 0);
                    $ledger->left_carry = max((float) $ledger->left_carry - $unmatchedVolume, 0);
                    $ledger->right_carry = (float) $ledger->right_carry + $pairVolume;
                } else {
                    $ledger->right_volume = max((float) $ledger->right_volume - (float) $plan->price, 0);
                    $ledger->right_carry = max((float) $ledger->right_carry - $unmatchedVolume, 0);
                    $ledger->left_carry = (float) $ledger->left_carry + $pairVolume;
                }

                $ledger->pair_volume = max((float) $ledger->pair_volume - $pairVolume, 0);
                $ledger->total_binary_bonus = max((float) $ledger->total_binary_bonus - (float) ($sourceTransaction?->amount ?? 0), 0);
                $ledger->save();
            }

            if ($sourceTransaction) {
                $reversalTransaction = $this->commissionLedgerService->reverseCredit(
                    $parent,
                    $sourceReference,
                    $sourceReference.':reversal',
                    MlmTransaction::TYPE_BINARY_BONUS_REVERSAL,
                    'Binary pair bonus reversal',
                    "{$member->name} subscription refund reversed binary pair bonus on your {$current->binary_position} leg.",
                    $refundRequestId,
                );

                $reversedAmount += (float) ($reversalTransaction?->amount ?? 0);
            }

            $current = $parent->fresh(['binaryParent']);
            $depth++;
        }

        return $reversedAmount;
    }

    private function pairVolumeFromSourceTransaction(?MlmTransaction $transaction): float
    {
        if (! $transaction || ! is_string($transaction->note)) {
            return 0.0;
        }

        preg_match('/generated\s+([0-9.]+)\s+pair volume/i', $transaction->note, $matches);

        return round((float) ($matches[1] ?? 0), 2);
    }
}
