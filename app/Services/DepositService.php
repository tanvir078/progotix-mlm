<?php

namespace App\Services;

use App\Models\MlmDepositRequest;
use App\Models\MlmPaymentMethod;
use App\Models\MlmTransaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepositService
{
    public function __construct(
        private readonly PaymentMethodService $paymentMethodService,
    ) {}

    public function submit(
        User $user,
        int $paymentMethodId,
        float $amount,
        ?string $senderName = null,
        ?string $senderAccount = null,
        ?string $transactionReference = null,
        ?UploadedFile $paymentProof = null,
        ?string $note = null,
    ): MlmDepositRequest {
        return DB::transaction(function () use (
            $user,
            $paymentMethodId,
            $amount,
            $senderName,
            $senderAccount,
            $transactionReference,
            $paymentProof,
            $note
        ): MlmDepositRequest {
            /** @var User $lockedUser */
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            $paymentMethod = $this->paymentMethodService->findAvailableForUser($lockedUser, $paymentMethodId, 'deposit');

            if (! $paymentMethod) {
                throw ValidationException::withMessages([
                    'payment_method_id' => 'The selected deposit method is not available for your account.',
                ]);
            }

            $chargePreview = $paymentMethod->chargePreview($amount);

            if ($chargePreview['net_amount'] <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'The deposit amount must stay above the configured charge for this method.',
                ]);
            }

            $proofPath = $paymentProof?->store('payments/deposits', 'local');

            return $lockedUser->depositRequests()->create([
                'payment_method_id' => $paymentMethod->id,
                'payment_method_name' => $paymentMethod->name,
                'payment_method_type' => $paymentMethod->type,
                'payment_method_snapshot' => $this->snapshot($paymentMethod),
                'currency' => $paymentMethod->currency_code,
                'amount' => $amount,
                'charge_amount' => $chargePreview['charge_amount'],
                'net_amount' => $chargePreview['net_amount'],
                'sender_name' => $senderName,
                'sender_account' => $senderAccount,
                'transaction_reference' => $transactionReference,
                'payment_proof_path' => $proofPath,
                'note' => $note,
                'status' => MlmDepositRequest::STATUS_PENDING,
                'submitted_at' => now(),
            ]);
        });
    }

    public function process(
        User $admin,
        MlmDepositRequest $depositRequest,
        string $decision,
        ?string $adminNote = null,
    ): MlmDepositRequest {
        return DB::transaction(function () use ($admin, $depositRequest, $decision, $adminNote): MlmDepositRequest {
            /** @var MlmDepositRequest $lockedRequest */
            $lockedRequest = MlmDepositRequest::query()
                ->lockForUpdate()
                ->findOrFail($depositRequest->id);

            if ($lockedRequest->status !== MlmDepositRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'decision' => 'This deposit request has already been processed.',
                ]);
            }

            /** @var User $lockedUser */
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($lockedRequest->user_id);

            $lockedRequest->forceFill([
                'status' => $decision === 'approve'
                    ? MlmDepositRequest::STATUS_APPROVED
                    : MlmDepositRequest::STATUS_REJECTED,
                'admin_note' => $adminNote,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ])->save();

            if ($decision === 'approve') {
                $lockedUser->forceFill([
                    'balance' => round((float) $lockedUser->balance + (float) $lockedRequest->net_amount, 2),
                ])->save();

                $lockedUser->transactions()->create([
                    'deposit_request_id' => $lockedRequest->id,
                    'type' => MlmTransaction::TYPE_DEPOSIT,
                    'direction' => 'credit',
                    'amount' => $lockedRequest->net_amount,
                    'title' => 'Deposit approved',
                    'note' => $lockedRequest->payment_method_name.' deposit approved after admin review.',
                    'posted_at' => now(),
                ]);
            }

            return $lockedRequest->fresh(['paymentMethod', 'processor', 'user']);
        });
    }

    /**
     * @return array<string, string|null>
     */
    private function snapshot(MlmPaymentMethod $paymentMethod): array
    {
        return [
            'name' => $paymentMethod->name,
            'type' => $paymentMethod->type,
            'provider_name' => $paymentMethod->provider_name,
            'country_code' => $paymentMethod->country_code,
            'currency_code' => $paymentMethod->currency_code,
            'destination_label' => $paymentMethod->destination_label,
            'destination_value' => $paymentMethod->destination_value,
            'instructions' => $paymentMethod->instructions,
        ];
    }
}
