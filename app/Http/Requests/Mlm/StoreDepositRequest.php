<?php

namespace App\Http\Requests\Mlm;

use App\Models\MlmPaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class StoreDepositRequest extends FormRequest
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
            'payment_method_id' => ['required', 'integer', 'exists:mlm_payment_methods,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'sender_name' => ['nullable', 'string', 'max:120'],
            'sender_account' => ['nullable', 'string', 'max:255'],
            'transaction_reference' => ['required', 'string', 'max:255'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sender_name' => $this->filled('sender_name') ? trim((string) $this->input('sender_name')) : null,
            'sender_account' => $this->filled('sender_account') ? trim((string) $this->input('sender_account')) : null,
            'transaction_reference' => trim((string) $this->input('transaction_reference')),
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

            if (! $paymentMethod || ! $paymentMethod->is_active || ! $paymentMethod->supports_deposit) {
                $validator->errors()->add('payment_method_id', 'The selected deposit method is not active.');

                return;
            }

            if ($paymentMethod->country_code && $paymentMethod->country_code !== $this->user()->country_code) {
                $validator->errors()->add('payment_method_id', 'This deposit method is not available in your country.');
            }

            $amount = round((float) $this->input('amount', 0), 2);

            if ($amount < (float) $paymentMethod->min_amount) {
                $validator->errors()->add('amount', 'The selected method requires a higher minimum deposit.');
            }

            if ($paymentMethod->max_amount !== null && $amount > (float) $paymentMethod->max_amount) {
                $validator->errors()->add('amount', 'The selected method exceeds the configured maximum deposit amount.');
            }

            if ($paymentMethod->chargePreview($amount)['net_amount'] <= 0) {
                $validator->errors()->add('amount', 'The deposit amount must stay above the configured charge for this method.');
            }
        });
    }

    public function paymentMethodId(): int
    {
        return (int) $this->validated('payment_method_id');
    }

    public function amount(): float
    {
        return round((float) $this->validated('amount'), 2);
    }

    public function senderName(): ?string
    {
        return $this->validated('sender_name');
    }

    public function senderAccount(): ?string
    {
        return $this->validated('sender_account');
    }

    public function transactionReference(): string
    {
        return trim((string) $this->validated('transaction_reference'));
    }

    public function paymentProof(): ?UploadedFile
    {
        /** @var UploadedFile|null $file */
        $file = $this->file('payment_proof');

        return $file;
    }

    public function note(): ?string
    {
        return $this->validated('note');
    }
}
