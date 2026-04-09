<?php

namespace App\Services;

use App\Models\MlmOrder;
use App\Models\MlmPlan;
use App\Models\MlmSubscription;
use App\Models\MlmTransaction;
use App\Models\User;
use Illuminate\Support\Collection;

class CommissionService
{
    public function __construct(
        private readonly CommissionLedgerService $commissionLedgerService,
    ) {}

    public function distributeSubscriptionBonuses(User $member, MlmPlan $plan, MlmSubscription $subscription): void
    {
        $uplineChain = $this->lockedReferralChain($member->referrer_id, 11);
        $directSponsor = $uplineChain->first();

        if ($directSponsor && (float) $plan->direct_bonus > 0) {
            $this->commissionLedgerService->credit(
                $directSponsor,
                $this->subscriptionReference($subscription, 'direct', $directSponsor),
                MlmTransaction::TYPE_DIRECT_BONUS,
                (float) $plan->direct_bonus,
                'Direct referral commission',
                "{$member->name} activated {$plan->name}.",
                $member,
                $subscription->id,
            );
        }

        if ((float) $plan->level_bonus <= 0) {
            return;
        }

        $teamDistribution = $this->subscriptionLevelDistribution();

        foreach ($teamDistribution as $level => $ratio) {
            /** @var User|null $earner */
            $earner = $uplineChain->get($level);

            if (! $earner) {
                break;
            }

            $amount = round((float) $plan->level_bonus * $ratio, 2);

            if ($amount <= 0) {
                continue;
            }

            $this->commissionLedgerService->credit(
                $earner,
                $this->subscriptionReference($subscription, 'level', $earner, $level),
                MlmTransaction::TYPE_LEVEL_BONUS,
                $amount,
                "Level {$level} commission",
                "{$member->name} generated a level {$level} team bonus from {$plan->name}.",
                $member,
                $subscription->id,
                commissionLevel: $level,
            );
        }
    }

    public function distributeRetailOrderCommissions(MlmOrder $order, User $buyer): void
    {
        $cycle = max(1, (int) $order->commission_cycle);

        if ((float) $order->commission_amount > 0) {
            $this->commissionLedgerService->credit(
                $buyer,
                $this->orderReference($order, $cycle, 'retail', $buyer),
                MlmTransaction::TYPE_RETAIL_COMMISSION,
                (float) $order->commission_amount,
                'Retail order commission',
                $this->orderNote($order, 'commission credited after payment confirmation.'),
                orderId: $order->id,
            );
        }

        if ((float) $order->team_bonus_amount <= 0) {
            return;
        }

        $uplineChain = $this->lockedReferralChain($buyer->referrer_id, count($this->retailTeamDistribution()));

        foreach ($this->retailTeamDistribution() as $level => $ratio) {
            /** @var User|null $earner */
            $earner = $uplineChain->get($level - 1);

            if (! $earner) {
                break;
            }

            $amount = round((float) $order->team_bonus_amount * $ratio, 2);

            if ($amount <= 0) {
                continue;
            }

            $this->commissionLedgerService->credit(
                $earner,
                $this->orderReference($order, $cycle, 'team', $earner, $level),
                MlmTransaction::TYPE_TEAM_SALES_BONUS,
                $amount,
                'Team retail sales bonus',
                $buyer->name." payment confirmation generated a level {$level} retail team bonus.",
                $buyer,
                orderId: $order->id,
                commissionLevel: $level,
            );
        }
    }

    public function reverseRetailOrderCommissions(MlmOrder $order, User $buyer): void
    {
        $cycle = max(1, (int) $order->commission_cycle);

        if ((float) $order->commission_amount > 0) {
            $this->commissionLedgerService->reverseCredit(
                $buyer,
                $this->orderReference($order, $cycle, 'retail', $buyer),
                $this->orderReversalReference($order, $cycle, 'retail', $buyer),
                MlmTransaction::TYPE_RETAIL_COMMISSION_REVERSAL,
                'Retail commission reversal',
                $this->orderNote($order, 'commission reversed after order cancellation.'),
            );
        }

        $uplineChain = $this->lockedReferralChain($buyer->referrer_id, count($this->retailTeamDistribution()));

        foreach ($this->retailTeamDistribution() as $level => $ratio) {
            /** @var User|null $earner */
            $earner = $uplineChain->get($level - 1);

            if (! $earner || round((float) $order->team_bonus_amount * $ratio, 2) <= 0) {
                continue;
            }

            $this->commissionLedgerService->reverseCredit(
                $earner,
                $this->orderReference($order, $cycle, 'team', $earner, $level),
                $this->orderReversalReference($order, $cycle, 'team', $earner, $level),
                MlmTransaction::TYPE_TEAM_SALES_BONUS_REVERSAL,
                'Team sales bonus reversal',
                $buyer->name." order was cancelled after payment confirmation on level {$level}.",
            );
        }
    }

    public function reverseSubscriptionBonuses(User $member, MlmSubscription $subscription): void
    {
        $uplineChain = $this->lockedReferralChain($member->referrer_id, 11);
        $directSponsor = $uplineChain->first();

        // Reverse direct bonus
        if ($directSponsor) {
            $directRef = $this->subscriptionReference($subscription, 'direct', $directSponsor);
            $this->commissionLedgerService->reverseCredit(
                $directSponsor,
                $directRef,
                $directRef . ':reversal',
                'direct_bonus_reversal',
                'Direct referral commission reversal',
                "{$member->name} subscription #{$subscription->id} was cancelled/refunded."
            );
        }

        // Reverse level bonuses
        $teamDistribution = $this->subscriptionLevelDistribution();

        foreach ($teamDistribution as $level => $ratio) {
            /** @var User|null $earner */
            $earner = $uplineChain->get($level);

            if (! $earner) {
                break;
            }

            $levelRef = $this->subscriptionReference($subscription, 'level', $earner, $level);
            $this->commissionLedgerService->reverseCredit(
                $earner,
                $levelRef,
                $levelRef . ':reversal',
                'level_bonus_reversal',
                "Level {$level} commission reversal",
                "{$member->name} subscription #{$subscription->id} generated reversal on level {$level}."
            );
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function lockedReferralChain(?int $startingUserId, int $maxDepth): Collection
    {
        $chain = collect();
        $currentUserId = $startingUserId;

        for ($depth = 1; $depth <= $maxDepth && $currentUserId; $depth++) {
            /** @var User|null $current */
            $current = User::query()
                ->lockForUpdate()
                ->find($currentUserId);

            if (! $current) {
                break;
            }

            $chain->push($current);
            $currentUserId = $current->referrer_id;
        }

        return $chain;
    }

    /**
     * @return array<int, float>
     */
    private function subscriptionLevelDistribution(): array
    {
        return collect(config('mlm.commission.subscription.level_distribution', []))
            ->mapWithKeys(fn (float|int|string $ratio, int|string $level): array => [(int) $level => (float) $ratio])
            ->sortKeys()
            ->all();
    }

    /**
     * @return array<int, float>
     */
    private function retailTeamDistribution(): array
    {
        return collect(config('mlm.commission.retail.team_distribution', [1 => 1.0]))
            ->mapWithKeys(fn (float|int|string $ratio, int|string $level): array => [(int) $level => (float) $ratio])
            ->sortKeys()
            ->all();
    }

    private function subscriptionReference(
        MlmSubscription $subscription,
        string $bucket,
        User $earner,
        ?int $level = null,
    ): string {
        $reference = "subscription:{$subscription->id}:{$bucket}:earner:{$earner->id}";

        if ($level !== null) {
            $reference .= ":level:{$level}";
        }

        return $reference;
    }

    private function orderReference(
        MlmOrder $order,
        int $cycle,
        string $bucket,
        User $earner,
        ?int $level = null,
    ): string {
        $reference = "order:{$order->id}:cycle:{$cycle}:{$bucket}:earner:{$earner->id}";

        if ($level !== null) {
            $reference .= ":level:{$level}";
        }

        return $reference;
    }

    private function orderReversalReference(
        MlmOrder $order,
        int $cycle,
        string $bucket,
        User $earner,
        ?int $level = null,
    ): string {
        return $this->orderReference($order, $cycle, $bucket, $earner, $level).':reversal';
    }

    private function orderNote(MlmOrder $order, string $suffix): string
    {
        $firstItem = $order->items->first();

        if (! $firstItem) {
            return 'Retail order '.$order->order_no.' '.$suffix;
        }

        return $firstItem->product_name.' x'.$firstItem->quantity.' order '.$suffix;
    }
}
