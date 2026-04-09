<?php

namespace App\Http\Requests\Mlm;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TransferWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'receiver_identity' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $identity = trim((string) ($this->input('receiver_identity') ?: $this->input('receiver_username')));

        $this->merge([
            'receiver_identity' => strtoupper($identity) === $identity ? $identity : strtolower($identity),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->user()) {
                return;
            }

            $receiverIdentity = (string) $this->input('receiver_identity');
            $currentUsername = strtolower((string) $this->user()->username);
            $currentMemberCode = strtoupper((string) $this->user()->member_code);

            if (
                $receiverIdentity !== ''
                && (
                    strtolower($receiverIdentity) === $currentUsername
                    || strtoupper($receiverIdentity) === $currentMemberCode
                )
            ) {
                $validator->errors()->add('receiver_identity', 'You cannot transfer to your own account.');
            }

            if ($receiverIdentity !== '' && ! User::query()
                ->where('username', strtolower($receiverIdentity))
                ->orWhere('member_code', strtoupper($receiverIdentity))
                ->exists()
            ) {
                $validator->errors()->add('receiver_identity', 'Member not found with this username or member code.');
            }

            $amount = round((float) $this->input('amount', 0), 2);

            if ($amount <= 0) {
                return;
            }

            $pendingTotal = (float) $this->user()->pendingWithdrawalTotal();
            $availableBalance = max(0, (float) $this->user()->balance - $pendingTotal);
            $feeRate = (float) config('mlm.wallet.transfer_fee_rate', 0.02);
            $totalDebit = round($amount + ($amount * $feeRate), 2);

            if ($totalDebit > $availableBalance) {
                $validator->errors()->add('amount', 'Insufficient transferable balance after fee.');
            }
        });
    }

    public function receiverIdentity(): string
    {
        return (string) $this->validated('receiver_identity');
    }

    public function amount(): float
    {
        return round((float) $this->validated('amount'), 2);
    }

    public function note(): ?string
    {
        $note = $this->validated('note');

        return $note !== null ? trim((string) $note) : null;
    }
}
