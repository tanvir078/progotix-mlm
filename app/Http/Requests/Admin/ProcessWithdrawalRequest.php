<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessWithdrawalRequest extends FormRequest
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
            'decision' => ['required', 'string', Rule::in(['approve', 'reject'])],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function decision(): string
    {
        return (string) $this->validated('decision');
    }

    public function adminNote(): ?string
    {
        $adminNote = $this->validated('admin_note');

        return is_string($adminNote) && trim($adminNote) !== '' ? trim($adminNote) : null;
    }
}
