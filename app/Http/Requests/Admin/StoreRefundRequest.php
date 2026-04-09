<?php

namespace App\Http\Requests\Admin;

use App\Models\MlmRefundRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in([
                MlmRefundRequest::TYPE_SUBSCRIPTION,
                MlmRefundRequest::TYPE_ORDER,
            ])],
            'subscription_id' => ['nullable', 'integer', 'required_if:type,'.MlmRefundRequest::TYPE_SUBSCRIPTION, 'exists:mlm_subscriptions,id'],
            'order_id' => ['nullable', 'integer', 'required_if:type,'.MlmRefundRequest::TYPE_ORDER, 'exists:mlm_orders,id'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function type(): string
    {
        return (string) $this->validated('type');
    }

    public function subscriptionId(): ?int
    {
        $value = $this->validated('subscription_id');

        return $value !== null ? (int) $value : null;
    }

    public function orderId(): ?int
    {
        $value = $this->validated('order_id');

        return $value !== null ? (int) $value : null;
    }

    public function reason(): ?string
    {
        $reason = $this->validated('reason');

        return is_string($reason) && trim($reason) !== '' ? trim($reason) : null;
    }

    public function adminNote(): ?string
    {
        $note = $this->validated('admin_note');

        return is_string($note) && trim($note) !== '' ? trim($note) : null;
    }
}
