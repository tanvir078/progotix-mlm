<?php

namespace App\Services;

use App\Models\MlmPaymentMethod;
use App\Models\MlmTransaction;
use App\Models\MlmWithdrawalRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawalService
{
    public function __construct(
        private readonly PaymentMethodService $paymentMethodService,
    ) {}

    public function submit(
        User $user,
        int $paymentMethodId,
        float $amount,
        string $accountDetails,
        ?string $note = null,
    ): MlmWithdrawalRequest {
        return DB::transaction(function () use ($user, $paymentMethodId, $amount, $accountDetails, $note): MlmWithdrawalRequest {
            /** @var User $lockedUser */
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            /** @var MlmPaymentMethod|null $paymentMethod */
            $paymentMethod = $this->paymentMethodService->findAvailableForUser($lockedUser, $paymentMethodId, 'withdrawal');

            if (! $paymentMethod) {
                throw ValidationException::withMessages([
                    'payment_method_id' => 'The selected payout method is not available for your account.',
                ]);
            }

            $pendingTotal = (float) $lockedUser->pendingWithdrawalTotal();
            $availableBalance = max(0, (float) $lockedUser->balance - $pendingTotal);

            if ($amount > $availableBalance) {
                throw ValidationException::withMessages([
                    'amount' => 'The requested withdrawal amount exceeds your available balance.',
                ]);
            }

            return $lockedUser->withdrawalRequests()->create([
                'payment_method_id' => $paymentMethod->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod->name,
                'account_details' => $accountDetails,
                'status' => MlmWithdrawalRequest::STATUS_PENDING,
                'note' => $note,
            ]);
        });
    }

    public function process(
        User $admin,
        MlmWithdrawalRequest $withdrawalRequest,
        string $decision,
        ?string $adminNote = null,
    ): MlmWithdrawalRequest {
        return DB::transaction(function () use ($admin, $withdrawalRequest, $decision, $adminNote): MlmWithdrawalRequest {
            /** @var MlmWithdrawalRequest $lockedRequest */
            $lockedRequest = MlmWithdrawalRequest::query()
                ->lockForUpdate()
                ->findOrFail($withdrawalRequest->id);

            if ($lockedRequest->status !== MlmWithdrawalRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'decision' => 'This withdrawal request has already been processed.',
                ]);
            }

            /** @var User $lockedUser */
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($lockedRequest->user_id);

            if ($decision === 'approve' && (float) $lockedUser->balance < (float) $lockedRequest->amount) {
                throw ValidationException::withMessages([
                    'decision' => 'Insufficient wallet balance to approve this withdrawal.',
                ]);
            }

            $lockedRequest->forceFill([
                'status' => $decision === 'approve'
                    ? MlmWithdrawalRequest::STATUS_APPROVED
                    : MlmWithdrawalRequest::STATUS_REJECTED,
                'admin_note' => $adminNote,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ])->save();

            if ($decision === 'approve') {
                $lockedUser->forceFill([
                    'balance' => round((float) $lockedUser->balance - (float) $lockedRequest->amount, 2),
                ])->save();

                $lockedUser->transactions()->create([
                    'type' => MlmTransaction::TYPE_WITHDRAWAL,
                    'direction' => 'debit',
                    'amount' => $lockedRequest->amount,
                    'title' => 'Withdrawal approved',
                    'note' => 'Admin approved a withdrawal request.',
                    'posted_at' => now(),
                ]);
            }

            return $lockedRequest->fresh(['user', 'processor']);
        });
    }
}
