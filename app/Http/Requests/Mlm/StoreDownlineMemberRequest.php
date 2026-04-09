<?php

namespace App\Http\Requests\Mlm;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDownlineMemberRequest extends FormRequest
{
    use ProfileValidationRules;

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
            ...$this->profileRules(),
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique(User::class, 'username')],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'placement_preference' => ['required', 'string', Rule::in(['auto', User::BINARY_LEFT, User::BINARY_RIGHT])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $country = collect(config('countries.list'))
            ->firstWhere('code', $this->input('country_code'));

        $this->merge([
            'username' => strtolower(trim((string) $this->input('username'))),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone_number' => trim((string) $this->input('phone_number')),
            'city' => $this->filled('city') ? trim((string) $this->input('city')) : null,
            'profession' => $this->filled('profession') ? trim((string) $this->input('profession')) : null,
            'company_name' => $this->filled('company_name') ? trim((string) $this->input('company_name')) : null,
            'profile_headline' => $this->filled('profile_headline') ? trim((string) $this->input('profile_headline')) : null,
            'bio' => $this->filled('bio') ? trim((string) $this->input('bio')) : null,
            'phone_code' => $country['dial_code'] ?? $this->input('phone_code'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->validated();
    }

    public function placementPreference(): string
    {
        return (string) $this->validated('placement_preference');
    }
}
