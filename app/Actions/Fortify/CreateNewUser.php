<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Services\MemberRegistrationService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private readonly MemberRegistrationService $memberRegistrationService,
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique(User::class, 'username')],
            'ref' => ['required', 'string', Rule::exists(User::class, 'username')],
            'password' => $this->passwordRules(),
        ])->validate();

        $sponsor = User::query()
            ->where('username', $input['ref'])
            ->firstOrFail();

        return $this->memberRegistrationService->registerUnderSponsor(
            $input,
            $sponsor,
        );
    }
}
