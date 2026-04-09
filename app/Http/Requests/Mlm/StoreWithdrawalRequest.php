<?php

namespace App\Http\Requests\Mlm;

use App\Models\MlmPaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreWithdrawalRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method_id' => ['required', 'integer', 'exists:mlm_payment_methods,id'],
            'account_details' => ['required', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'account_details' => trim((string) $this->input('account_details')),
            'note' => $this->filled('note') ? trim((string) $this->input('note')) : null,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->user()) {
                return;
            }

            $paymentMethod = MlmPaymentMethod::query()->find($this->input('payment_method_id'));

            if (! $paymentMethod || ! $paymentMethod->is_active || ! $paymentMethod->supports_withdrawal) {
                $validator->errors()->add('payment_method_id', 'The selected payout method is not active.');

                return;
            }

            if ($paymentMethod->country_code && $paymentMethod->country_code !== $this->user()->country_code) {
                $validator->errors()->add('payment_method_id', 'This payout method is not available in your country.');
            }

            $amount = round((float) $this->input('amount', 0), 2);

            if ($amount <= 0) {
                return;
            }

            if ($amount < (float) $paymentMethod->min_amount) {
                $validator->errors()->add('amount', 'The selected method requires a higher minimum withdrawal.');
            }

            if ($paymentMethod->max_amount !== null && $amount > (float) $paymentMethod->max_amount) {
                $validator->errors()->add('amount', 'The selected method exceeds the configured maximum withdrawal amount.');
            }

            $pendingTotal = (float) $this->user()->pendingWithdrawalTotal();
            $availableBalance = max(0, (float) $this->user()->balance - $pendingTotal);

            if ($amount > $availableBalance) {
                $validator->errors()->add('amount', 'The requested withdrawal amount exceeds your available balance.');
            }
        });
    }

    public function amount(): float
    {
        return round((float) $this->validated('amount'), 2);
    }

    public function paymentMethod(): string
    {
        return (string) $this->validated('payment_method_id');
    }

    public function paymentMethodId(): int
    {
        return (int) $this->validated('payment_method_id');
    }

    public function accountDetails(): string
    {
        return trim((string) $this->validated('account_details'));
    }

    public function note(): ?string
    {
        $note = $this->validated('note');

        return $note !== null ? trim((string) $note) : null;
    }
}
