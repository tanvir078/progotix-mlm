<?php

namespace App\Services;

use App\Models\MlmInvoice;
use App\Models\MlmOrder;
use App\Models\MlmRefundRequest;
use App\Models\MlmSubscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundWorkflowService
{
    public function __construct(
        private readonly BinaryBonusService $binaryBonusService,
        private readonly CommissionRuleService $commissionRuleService,
        private readonly CommissionService $commissionService,
        private readonly RankService $rankService,
    ) {}

    public function submitSubscriptionRefund(
        User $admin,
        MlmSubscription $subscription,
        ?string $reason = null,
        ?string $adminNote = null,
    ): MlmRefundRequest {
        return DB::transaction(function () use ($admin, $subscription, $reason, $adminNote): MlmRefundRequest {
            /** @var MlmSubscription $lockedSubscription */
            $lockedSubscription = MlmSubscription::query()
                ->lockForUpdate()
                ->with(['user', 'plan'])
                ->findOrFail($subscription->id);

            $this->ensureSubscriptionCanEnterRefundQueue($lockedSubscription);

            /** @var MlmInvoice|null $invoice */
            $invoice = MlmInvoice::query()
                ->lockForUpdate()
                ->where('subscription_id', $lockedSubscription->id)
                ->latest('id')
                ->first();

            $existing = MlmRefundRequest::query()
                ->lockForUpdate()
                ->where('subscription_id', $lockedSubscription->id)
                ->where('status', MlmRefundRequest::STATUS_PENDING)
                ->first();

            if ($existing) {
                return $existing->fresh(['user', 'subscription.plan', 'invoice']);
            }

            $lockedSubscription->forceFill([
                'refund_requested_at' => now(),
                'refund_note' => $adminNote ?? $lockedSubscription->refund_note,
            ])->save();

            /** @var MlmRefundRequest $refundRequest */
            $refundRequest = MlmRefundRequest::query()->create([
                'user_id' => $lockedSubscription->user_id,
                'subscription_id' => $lockedSubscription->id,
                'invoice_id' => $invoice?->id,
                'requested_by' => $admin->id,
                'type' => MlmRefundRequest::TYPE_SUBSCRIPTION,
                'status' => MlmRefundRequest::STATUS_PENDING,
                'amount' => $lockedSubscription->amount,
                'reason' => $reason,
                'admin_note' => $adminNote,
                'requested_at' => now(),
            ]);

            return $refundRequest->fresh(['user', 'subscription.plan', 'invoice']);
        });
    }

    public function submitOrderRefund(
        User $admin,
        MlmOrder $order,
        ?string $reason = null,
        ?string $adminNote = null,
    ): MlmRefundRequest {
        return DB::transaction(function () use ($admin, $order, $reason, $adminNote): MlmRefundRequest {
            /** @var MlmOrder $lockedOrder */
            $lockedOrder = MlmOrder::query()
                ->lockForUpdate()
                ->with(['user', 'items', 'invoice'])
                ->findOrFail($order->id);

            $this->ensureOrderCanEnterRefundQueue($lockedOrder);

            $existing = MlmRefundRequest::query()
                ->lockForUpdate()
                ->where('order_id', $lockedOrder->id)
                ->where('status', MlmRefundRequest::STATUS_PENDING)
                ->first();

            if ($existing) {
                return $existing->fresh(['user', 'order.items', 'invoice']);
            }

            $lockedOrder->forceFill([
                'refund_requested_at' => now(),
                'refund_note' => $adminNote ?? $lockedOrder->refund_note,
            ])->save();

            /** @var MlmRefundRequest $refundRequest */
            $refundRequest = MlmRefundRequest::query()->create([
                'user_id' => $lockedOrder->user_id,
                'order_id' => $lockedOrder->id,
                'invoice_id' => $lockedOrder->invoice?->id,
                'requested_by' => $admin->id,
                'type' => MlmRefundRequest::TYPE_ORDER,
                'status' => MlmRefundRequest::STATUS_PENDING,
                'amount' => $lockedOrder->subtotal,
                'reason' => $reason,
                'admin_note' => $adminNote,
                'requested_at' => now(),
            ]);

            return $refundRequest->fresh(['user', 'order.items', 'invoice']);
        });
    }

    public function process(
        User $admin,
        MlmRefundRequest $refundRequest,
        string $decision,
        ?string $adminNote = null,
    ): MlmRefundRequest {
        return DB::transaction(function () use ($admin, $refundRequest, $decision, $adminNote): MlmRefundRequest {
            /** @var MlmRefundRequest $lockedRefundRequest */
            $lockedRefundRequest = MlmRefundRequest::query()
                ->lockForUpdate()
                ->with(['subscription.plan', 'order.items', 'invoice', 'user'])
                ->findOrFail($refundRequest->id);

            if ($lockedRefundRequest->status !== MlmRefundRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'decision' => 'This refund request has already been processed.',
                ]);
            }

            if ($decision === 'reject') {
                $this->rejectRefund($admin, $lockedRefundRequest, $adminNote);

                return $lockedRefundRequest->fresh(['subscription.plan', 'order.items', 'invoice', 'user']);
            }

            $commissionReversalAmount = match ($lockedRefundRequest->type) {
                MlmRefundRequest::TYPE_SUBSCRIPTION => $this->approveSubscriptionRefund($admin, $lockedRefundRequest, $adminNote),
                MlmRefundRequest::TYPE_ORDER => $this->approveOrderRefund($admin, $lockedRefundRequest, $adminNote),
                default => throw ValidationException::withMessages([
                    'type' => 'Unsupported refund request type.',
                ]),
            };

            $lockedRefundRequest->forceFill([
                'status' => MlmRefundRequest::STATUS_APPROVED,
                'processed_by' => $admin->id,
                'processed_at' => now(),
                'admin_note' => $adminNote ?? $lockedRefundRequest->admin_note,
                'commission_reversal_amount' => round($commissionReversalAmount, 2),
            ])->save();

            return $lockedRefundRequest->fresh(['subscription.plan', 'order.items', 'invoice', 'user', 'transactions']);
        });
    }

    private function approveSubscriptionRefund(
        User $admin,
        MlmRefundRequest $refundRequest,
        ?string $adminNote = null,
    ): float {
        /** @var MlmSubscription $subscription */
        $subscription = MlmSubscription::query()
            ->lockForUpdate()
            ->with(['plan', 'user'])
            ->findOrFail($refundRequest->subscription_id);

        if ($subscription->status === MlmSubscription::STATUS_REFUNDED) {
            throw ValidationException::withMessages([
                'subscription' => 'This subscription has already been refunded.',
            ]);
        }

        /** @var User $member */
        $member = User::query()
            ->lockForUpdate()
            ->findOrFail($subscription->user_id);

        /** @var MlmInvoice|null $invoice */
        $invoice = MlmInvoice::query()
            ->lockForUpdate()
            ->find($refundRequest->invoice_id);

        $commissionReversalAmount = 0.0;

        if ($this->shouldReverseCommissions()) {
            $commissionReversalAmount += $this->commissionService->reverseSubscriptionBonuses(
                $member,
                $subscription,
                $refundRequest->id,
            );

            $commissionReversalAmount += $this->binaryBonusService->reverse(
                $member,
                $subscription->plan,
                $subscription,
                $refundRequest->id,
            );
        }

        $subscription->forceFill([
            'status' => MlmSubscription::STATUS_REFUNDED,
            'refunded_at' => now(),
            'refunded_by' => $admin->id,
            'refund_note' => $adminNote ?? $refundRequest->reason ?? $subscription->refund_note,
        ])->save();

        if ($invoice) {
            $invoice->forceFill([
                'status' => MlmInvoice::STATUS_REFUNDED,
                'refunded_at' => now(),
                'refund_note' => $adminNote ?? $refundRequest->reason ?? $invoice->refund_note,
            ])->save();
        }

        $this->syncMemberAndReferrerRanks($member);

        return $commissionReversalAmount;
    }

    private function approveOrderRefund(
        User $admin,
        MlmRefundRequest $refundRequest,
        ?string $adminNote = null,
    ): float {
        /** @var MlmOrder $order */
        $order = MlmOrder::query()
            ->lockForUpdate()
            ->with(['items', 'invoice', 'user'])
            ->findOrFail($refundRequest->order_id);

        if ($order->status !== MlmOrder::STATUS_PAID) {
            throw ValidationException::withMessages([
                'order' => 'Only paid orders can be refunded.',
            ]);
        }

        /** @var User $member */
        $member = User::query()
            ->lockForUpdate()
            ->findOrFail($order->user_id);

        $commissionReversalAmount = 0.0;

        if ($this->shouldReverseCommissions()) {
            $commissionReversalAmount = $this->commissionService->reverseRetailOrderCommissions(
                $order->fresh(['items']),
                $member,
                $refundRequest->id,
            );
        }

        $order->forceFill([
            'status' => MlmOrder::STATUS_REFUNDED,
            'refunded_at' => now(),
            'refunded_by' => $admin->id,
            'refund_note' => $adminNote ?? $refundRequest->reason ?? $order->refund_note,
        ])->save();

        if ($order->invoice) {
            $order->invoice->forceFill([
                'status' => MlmInvoice::STATUS_REFUNDED,
                'refunded_at' => now(),
                'refund_note' => $adminNote ?? $refundRequest->reason ?? $order->invoice->refund_note,
            ])->save();
        }

        $this->syncMemberAndReferrerRanks($member);

        return $commissionReversalAmount;
    }

    private function rejectRefund(
        User $admin,
        MlmRefundRequest $refundRequest,
        ?string $adminNote = null,
    ): void {
        if ($refundRequest->type === MlmRefundRequest::TYPE_SUBSCRIPTION && $refundRequest->subscription_id) {
            MlmSubscription::query()
                ->whereKey($refundRequest->subscription_id)
                ->update([
                    'refund_requested_at' => null,
                    'refund_note' => $adminNote,
                ]);
        }

        if ($refundRequest->type === MlmRefundRequest::TYPE_ORDER && $refundRequest->order_id) {
            MlmOrder::query()
                ->whereKey($refundRequest->order_id)
                ->update([
                    'refund_requested_at' => null,
                    'refund_note' => $adminNote,
                ]);
        }

        $refundRequest->forceFill([
            'status' => MlmRefundRequest::STATUS_REJECTED,
            'processed_by' => $admin->id,
            'processed_at' => now(),
            'admin_note' => $adminNote ?? $refundRequest->admin_note,
        ])->save();
    }

    private function ensureSubscriptionCanEnterRefundQueue(MlmSubscription $subscription): void
    {
        if (! (bool) data_get($this->commissionRuleService->refundPolicy(), 'allow_subscription_refunds', true)) {
            throw ValidationException::withMessages([
                'subscription' => 'Subscription refunds are currently disabled.',
            ]);
        }

        if (! in_array($subscription->status, [
            MlmSubscription::STATUS_ACTIVE,
            MlmSubscription::STATUS_UPGRADED,
        ], true)) {
            throw ValidationException::withMessages([
                'subscription' => 'Only active or upgraded subscriptions can enter the refund queue.',
            ]);
        }
    }

    private function ensureOrderCanEnterRefundQueue(MlmOrder $order): void
    {
        if (! (bool) data_get($this->commissionRuleService->refundPolicy(), 'allow_order_refunds', true)) {
            throw ValidationException::withMessages([
                'order' => 'Order refunds are currently disabled.',
            ]);
        }

        if ($order->status !== MlmOrder::STATUS_PAID) {
            throw ValidationException::withMessages([
                'order' => 'Only paid orders can enter the refund queue.',
            ]);
        }
    }

    private function shouldReverseCommissions(): bool
    {
        return (bool) data_get($this->commissionRuleService->refundPolicy(), 'auto_reverse_commissions', true);
    }

    private function syncMemberAndReferrerRanks(User $member): void
    {
        $this->rankService->sync($member->fresh());

        if ($member->referrer_id) {
            $this->rankService->sync(
                User::query()->findOrFail($member->referrer_id),
            );
        }
    }
}
