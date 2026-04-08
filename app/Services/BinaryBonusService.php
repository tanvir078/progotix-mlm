<?php

namespace App\Services;

use App\Models\MlmPlan;
use App\Models\MlmSubscription;
use App\Models\MlmTransaction;
use App\Models\User;

class BinaryBonusService
{
    public function distribute(User $member, MlmPlan $plan, MlmSubscription $subscription): void
    {
        $current = $member->fresh(['binaryParent']);

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
                'bonus_rate' => 0.10,
                'total_binary_bonus' => 0,
            ]);

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
                    $parent->transactions()->create([
                        'source_user_id' => $member->id,
                        'subscription_id' => $subscription->id,
                        'type' => MlmTransaction::TYPE_BINARY_BONUS,
                        'direction' => 'credit',
                        'amount' => $bonusAmount,
                        'title' => 'Binary pair bonus',
                        'note' => "{$member->name} generated {$pairVolume} pair volume on your {$current->binary_position} leg.",
                        'posted_at' => now(),
                    ]);

                    $parent->increment('balance', $bonusAmount);
                }
            }

            $ledger->save();

            $current = $parent->fresh(['binaryParent']);
        }
    }
}
