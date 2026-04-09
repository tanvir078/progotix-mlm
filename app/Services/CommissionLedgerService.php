<?php

namespace App\Services;

use App\Models\MlmTransaction;
use App\Models\User;

class CommissionLedgerService
{
    public function credit(
        User $user,
        string $referenceKey,
        string $type,
        float $amount,
        string $title,
        ?string $note = null,
        ?User $sourceUser = null,
        ?int $subscriptionId = null,
        ?int $orderId = null,
        ?int $refundRequestId = null,
        ?int $commissionLevel = null,
    ): ?MlmTransaction {
        if ($amount <= 0) {
            return null;
        }

        /** @var User $lockedUser */
        $lockedUser = User::query()
            ->lockForUpdate()
            ->findOrFail($user->id);

        $existing = MlmTransaction::query()
            ->where('reference_key', $referenceKey)
            ->first();

        if ($existing) {
            return $existing;
        }

        $transaction = $lockedUser->transactions()->create([
            'source_user_id' => $sourceUser?->id,
            'subscription_id' => $subscriptionId,
            'order_id' => $orderId,
            'refund_request_id' => $refundRequestId,
            'reference_key' => $referenceKey,
            'commission_level' => $commissionLevel,
            'type' => $type,
            'direction' => 'credit',
            'amount' => round($amount, 2),
            'title' => $title,
            'note' => $note,
            'posted_at' => now(),
        ]);

        $lockedUser->increment('balance', round($amount, 2));

        return $transaction;
    }

    public function reverseCredit(
        User $user,
        string $sourceReferenceKey,
        string $reversalReferenceKey,
        string $reversalType,
        string $title,
        ?string $note = null,
        ?int $refundRequestId = null,
    ): ?MlmTransaction {
        /** @var User $lockedUser */
        $lockedUser = User::query()
            ->lockForUpdate()
            ->findOrFail($user->id);

        $existing = MlmTransaction::query()
            ->where('reference_key', $reversalReferenceKey)
            ->first();

        if ($existing) {
            return $existing;
        }

        $sourceTransaction = MlmTransaction::query()
            ->where('reference_key', $sourceReferenceKey)
            ->first();

        if (! $sourceTransaction) {
            return null;
        }

        $transaction = $lockedUser->transactions()->create([
            'source_user_id' => $sourceTransaction->source_user_id,
            'subscription_id' => $sourceTransaction->subscription_id,
            'order_id' => $sourceTransaction->order_id,
            'refund_request_id' => $refundRequestId,
            'reference_key' => $reversalReferenceKey,
            'commission_level' => $sourceTransaction->commission_level,
            'type' => $reversalType,
            'direction' => 'debit',
            'amount' => $sourceTransaction->amount,
            'title' => $title,
            'note' => $note,
            'posted_at' => now(),
        ]);

        $lockedUser->decrement('balance', (float) $sourceTransaction->amount);

        return $transaction;
    }
}
