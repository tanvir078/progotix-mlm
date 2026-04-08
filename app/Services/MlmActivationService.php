<?php

namespace App\Services;

use App\Models\MlmInvoice;
use App\Models\MlmPlan;
use App\Models\MlmSubscription;
use App\Models\MlmTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MlmActivationService
{
    public function __construct(
        private readonly BinaryBonusService $binaryBonusService,
    ) {
    }

    /**
     * @return array{subscription: \App\Models\MlmSubscription, invoice: \App\Models\MlmInvoice}
     */
    public function activate(User $user, MlmPlan $plan): array
    {
        return DB::transaction(function () use ($user, $plan): array {
            $user->subscriptions()
                ->where('status', MlmSubscription::STATUS_ACTIVE)
                ->update(['status' => MlmSubscription::STATUS_UPGRADED]);

            $subscription = $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'sponsor_id' => $user->referrer_id,
                'amount' => $plan->price,
                'status' => MlmSubscription::STATUS_ACTIVE,
                'started_at' => now(),
            ]);

            $invoice = $user->invoices()->create([
                'subscription_id' => $subscription->id,
                'invoice_no' => $this->nextInvoiceNo(),
                'title' => "{$plan->name} package activation",
                'amount' => $plan->price,
                'status' => MlmInvoice::STATUS_PAID,
                'issued_at' => now(),
                'due_at' => now(),
                'paid_at' => now(),
                'notes' => 'Auto-generated after package activation.',
            ]);

            $this->creditDirectBonus($user, $plan, $subscription);
            $this->creditLevelBonuses($user, $plan, $subscription);
            $this->binaryBonusService->distribute($user, $plan, $subscription);

            return [
                'subscription' => $subscription,
                'invoice' => $invoice,
            ];
        });
    }

    private function creditDirectBonus(User $user, MlmPlan $plan, MlmSubscription $subscription): void
    {
        if (! $user->referrer || $plan->direct_bonus <= 0) {
            return;
        }

        $user->referrer->transactions()->create([
            'source_user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'type' => MlmTransaction::TYPE_DIRECT_BONUS,
            'direction' => 'credit',
            'amount' => $plan->direct_bonus,
            'title' => 'Direct referral commission',
            'note' => "{$user->name} activated {$plan->name}.",
            'posted_at' => now(),
        ]);

        $user->referrer->increment('balance', $plan->direct_bonus);
    }

    private function creditLevelBonuses(User $user, MlmPlan $plan, MlmSubscription $subscription): void
    {
        if ($plan->level_bonus <= 0) {
            return;
        }

        $distribution = [
            1 => 0.5,
            2 => 0.3,
            3 => 0.2,
        ];

        $ancestor = $user->referrer?->referrer;

        foreach ($distribution as $level => $ratio) {
            if (! $ancestor) {
                break;
            }

            $amount = round((float) $plan->level_bonus * $ratio, 2);

            if ($amount > 0) {
                $ancestor->transactions()->create([
                    'source_user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'type' => MlmTransaction::TYPE_LEVEL_BONUS,
                    'direction' => 'credit',
                    'amount' => $amount,
                    'title' => "Level {$level} commission",
                    'note' => "{$user->name} generated a level {$level} team bonus.",
                    'posted_at' => now(),
                ]);

                $ancestor->increment('balance', $amount);
            }

            $ancestor = $ancestor->referrer;
        }
    }

    private function nextInvoiceNo(): string
    {
        $nextId = (int) (MlmInvoice::query()->max('id') ?? 0) + 1;

        return 'INV-'.now()->format('Ymd').'-'.str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }
}
