<?php

namespace App\Http\Requests\Admin;

use App\Models\MlmPaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertPaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'country_code' => $this->filled('country_code') ? strtoupper(trim((string) $this->input('country_code'))) : null,
            'currency_code' => strtoupper(trim((string) $this->input('currency_code'))),
            'provider_name' => $this->filled('provider_name') ? trim((string) $this->input('provider_name')) : null,
            'destination_label' => $this->filled('destination_label') ? trim((string) $this->input('destination_label')) : null,
            'destination_value' => $this->filled('destination_value') ? trim((string) $this->input('destination_value')) : null,
            'instructions' => $this->filled('instructions') ? trim((string) $this->input('instructions')) : null,
            'supports_deposit' => $this->boolean('supports_deposit'),
            'supports_withdrawal' => $this->boolean('supports_withdrawal'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->filled('sort_order') ? (int) $this->input('sort_order') : 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $paymentMethodId = $this->route('paymentMethod')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:60', Rule::unique('mlm_payment_methods', 'code')->ignore($paymentMethodId)],
            'type' => ['required', 'string', Rule::in([
                MlmPaymentMethod::TYPE_E_WALLET,
                MlmPaymentMethod::TYPE_BANK,
                MlmPaymentMethod::TYPE_CARD,
                MlmPaymentMethod::TYPE_CRYPTO,
            ])],
            'country_code' => ['nullable', 'string', 'size:2'],
            'currency_code' => ['required', 'string', 'max:8'],
            'provider_name' => ['nullable', 'string', 'max:120'],
            'destination_label' => ['nullable', 'string', 'max:120'],
            'destination_value' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'fixed_charge' => ['nullable', 'numeric', 'min:0'],
            'percent_charge_rate' => ['nullable', 'numeric', 'between:0,1'],
            'supports_deposit' => ['required', 'boolean'],
            'supports_withdrawal' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->boolean('supports_deposit') && ! $this->boolean('supports_withdrawal')) {
                $validator->errors()->add('supports_deposit', 'Enable at least one flow for this payment method.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'name' => trim((string) $this->validated('name')),
            'code' => strtoupper((string) $this->validated('code')),
            'type' => (string) $this->validated('type'),
            'country_code' => $this->validated('country_code'),
            'currency_code' => strtoupper((string) $this->validated('currency_code')),
            'provider_name' => $this->validated('provider_name'),
            'destination_label' => $this->validated('destination_label'),
            'destination_value' => $this->validated('destination_value'),
            'instructions' => $this->validated('instructions'),
            'min_amount' => round((float) $this->validated('min_amount'), 2),
            'max_amount' => $this->validated('max_amount') !== null ? round((float) $this->validated('max_amount'), 2) : null,
            'fixed_charge' => round((float) ($this->validated('fixed_charge') ?? 0), 2),
            'percent_charge_rate' => round((float) ($this->validated('percent_charge_rate') ?? 0), 4),
            'supports_deposit' => (bool) $this->validated('supports_deposit'),
            'supports_withdrawal' => (bool) $this->validated('supports_withdrawal'),
            'is_active' => (bool) $this->validated('is_active'),
            'sort_order' => (int) ($this->validated('sort_order') ?? 0),
        ];
    }
}
