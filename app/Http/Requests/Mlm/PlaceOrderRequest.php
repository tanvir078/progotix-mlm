<?php

namespace App\Http\Requests\Mlm;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
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
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function quantity(): int
    {
        return (int) $this->validated('quantity');
    }
}
