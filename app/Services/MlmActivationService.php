<?php

namespace App\Services;

use App\Models\MlmInvoice;
use App\Models\MlmPlan;
use App\Models\MlmSubscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MlmActivationService
{
    public function __construct(
        private readonly CommissionService $commissionService,
        private readonly BinaryBonusService $binaryBonusService,
        private readonly ReferenceNumberService $referenceNumberService,
    ) {}

    /**
     * @return array{subscription: MlmSubscription, invoice: MlmInvoice}
     */
    public function activate(User $user, MlmPlan $plan): array
    {
        return DB::transaction(function () use ($user, $plan): array {
            /** @var User $lockedUser */
            $lockedUser = User::query()
                ->lockForUpdate()
                ->with('referrer')
                ->findOrFail($user->id);

            $lockedUser->subscriptions()
                ->where('status', MlmSubscription::STATUS_ACTIVE)
                ->update(['status' => MlmSubscription::STATUS_UPGRADED]);

            $subscription = $lockedUser->subscriptions()->create([
                'plan_id' => $plan->id,
                'sponsor_id' => $lockedUser->referrer_id,
                'amount' => $plan->price,
                'status' => MlmSubscription::STATUS_ACTIVE,
                'started_at' => now(),
            ]);

            $invoice = $lockedUser->invoices()->create([
                'subscription_id' => $subscription->id,
                'invoice_no' => $this->referenceNumberService->nextInvoiceNo(),
                'title' => "{$plan->name} package activation",
                'amount' => $plan->price,
                'status' => MlmInvoice::STATUS_PAID,
                'issued_at' => now(),
                'due_at' => now(),
                'paid_at' => now(),
                'notes' => 'Auto-generated after package activation.',
            ]);

            $this->commissionService->distributeSubscriptionBonuses($lockedUser, $plan, $subscription);
            $this->binaryBonusService->distribute($lockedUser, $plan, $subscription);

            return [
                'subscription' => $subscription,
                'invoice' => $invoice,
            ];
        });
    }
}
