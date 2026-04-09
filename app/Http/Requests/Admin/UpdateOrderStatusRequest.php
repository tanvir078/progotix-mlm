<?php

namespace App\Http\Requests\Admin;

use App\Models\MlmOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in([
                MlmOrder::STATUS_PAID,
                MlmOrder::STATUS_PENDING,
                MlmOrder::STATUS_CANCELLED,
            ])],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function status(): string
    {
        return (string) $this->validated('status');
    }

    public function notes(): ?string
    {
        $notes = $this->validated('notes');

        return is_string($notes) && trim($notes) !== '' ? trim($notes) : null;
    }
}
