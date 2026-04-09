<?php

namespace App\Services;

use App\Models\MlmInvoice;
use App\Models\MlmOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderWorkflowService
{
    public function __construct(
        private readonly CommissionService $commissionService,
        private readonly RankService $rankService,
        private readonly ReferenceNumberService $referenceNumberService,
    ) {}

    public function transition(MlmOrder $order, string $targetStatus, ?string $notes = null): MlmOrder
    {
        return DB::transaction(function () use ($order, $targetStatus, $notes): MlmOrder {
            /** @var MlmOrder $lockedOrder */
            $lockedOrder = MlmOrder::query()
                ->lockForUpdate()
                ->with(['items', 'invoice', 'user'])
                ->findOrFail($order->id);

            $currentStatus = $lockedOrder->status;

            if ($currentStatus === $targetStatus) {
                if ($notes !== null) {
                    $lockedOrder->forceFill([
                        'notes' => $notes,
                    ])->save();
                }

                return $lockedOrder->fresh(['items', 'invoice', 'user']);
            }

            $this->ensureTransitionIsAllowed($currentStatus, $targetStatus);

            /** @var User $lockedUser */
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($lockedOrder->user_id);

            /** @var User|null $lockedReferrer */
            $lockedReferrer = $lockedUser->referrer_id
                ? User::query()->lockForUpdate()->find($lockedUser->referrer_id)
                : null;

            $invoice = $this->resolveInvoice($lockedOrder, $lockedUser);

            match ($targetStatus) {
                MlmOrder::STATUS_PENDING => $this->markPending($lockedOrder, $invoice, $notes),
                MlmOrder::STATUS_PAID => $this->markPaid($lockedOrder, $invoice, $lockedUser, $notes),
                MlmOrder::STATUS_CANCELLED => $this->markCancelled($lockedOrder, $invoice, $lockedUser, $notes),
            };

            $this->rankService->sync($lockedUser->fresh());

            if ($lockedReferrer) {
                $this->rankService->sync($lockedReferrer->fresh());
            }

            return $lockedOrder->fresh(['items', 'invoice', 'user']);
        });
    }

    private function ensureTransitionIsAllowed(string $currentStatus, string $targetStatus): void
    {
        $allowedTransitions = [
            MlmOrder::STATUS_PENDING => [
                MlmOrder::STATUS_PAID,
                MlmOrder::STATUS_CANCELLED,
            ],
            MlmOrder::STATUS_PAID => [
                MlmOrder::STATUS_CANCELLED,
            ],
            MlmOrder::STATUS_CANCELLED => [
                MlmOrder::STATUS_PENDING,
                MlmOrder::STATUS_PAID,
            ],
        ];

        if (! in_array($targetStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => 'The selected order status transition is not allowed.',
            ]);
        }
    }

    private function resolveInvoice(MlmOrder $order, User $user): MlmInvoice
    {
        $invoice = MlmInvoice::query()
            ->lockForUpdate()
            ->where('order_id', $order->id)
            ->first();

        if ($invoice) {
            return $invoice;
        }

        $existingInvoice = MlmInvoice::query()
            ->lockForUpdate()
            ->where('user_id', $user->id)
            ->whereNull('subscription_id')
            ->where('title', 'Retail order '.$order->order_no)
            ->latest('id')
            ->first();

        if ($existingInvoice) {
            $existingInvoice->forceFill([
                'order_id' => $order->id,
            ])->save();

            return $existingInvoice;
        }

        return $user->invoices()->create([
            'order_id' => $order->id,
            'invoice_no' => $this->referenceNumberService->nextInvoiceNo(),
            'title' => 'Retail order '.$order->order_no,
            'amount' => $order->subtotal,
            'status' => MlmInvoice::STATUS_PENDING,
            'issued_at' => $order->placed_at ?? now(),
            'due_at' => $order->placed_at ?? now(),
            'notes' => 'Auto-generated from retail order flow.',
        ]);
    }

    private function markPending(MlmOrder $order, MlmInvoice $invoice, ?string $notes): void
    {
        $order->forceFill([
            'status' => MlmOrder::STATUS_PENDING,
            'paid_at' => null,
            'notes' => $notes ?? $order->notes,
        ])->save();

        $invoice->forceFill([
            'status' => MlmInvoice::STATUS_PENDING,
            'paid_at' => null,
            'notes' => $notes ?? $invoice->notes,
        ])->save();
    }

    private function markPaid(
        MlmOrder $order,
        MlmInvoice $invoice,
        User $user,
        ?string $notes,
    ): void {
        $paidAt = $order->paid_at ?? now();

        $order->forceFill([
            'status' => MlmOrder::STATUS_PAID,
            'commission_cycle' => $order->status === MlmOrder::STATUS_PAID
                ? $order->commission_cycle
                : ((int) $order->commission_cycle + 1),
            'paid_at' => $paidAt,
            'notes' => $notes ?? $order->notes,
        ])->save();

        $invoice->forceFill([
            'status' => MlmInvoice::STATUS_PAID,
            'paid_at' => $invoice->paid_at ?? $paidAt,
            'notes' => $notes ?? $invoice->notes,
        ])->save();

        $this->commissionService->distributeRetailOrderCommissions($order->fresh(['items']), $user);
    }

    private function markCancelled(
        MlmOrder $order,
        MlmInvoice $invoice,
        User $user,
        ?string $notes,
    ): void {
        $wasPaid = $order->status === MlmOrder::STATUS_PAID;

        $order->forceFill([
            'status' => MlmOrder::STATUS_CANCELLED,
            'notes' => $notes ?? $order->notes,
        ])->save();

        $invoice->forceFill([
            'status' => MlmInvoice::STATUS_CANCELLED,
            'notes' => $notes ?? $invoice->notes,
        ])->save();

        if (! $wasPaid) {
            return;
        }

        $this->commissionService->reverseRetailOrderCommissions($order->fresh(['items']), $user);
    }
}
