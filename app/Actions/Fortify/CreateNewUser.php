<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Services\BinaryTreeService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private readonly BinaryTreeService $binaryTreeService,
    ) {
    }

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
            'ref' => ['nullable', 'string', Rule::exists(User::class, 'username')],
            'password' => $this->passwordRules(),
        ])->validate();

        $referrerId = null;

        if (! empty($input['ref'])) {
            $referrerId = User::query()
                ->where('username', $input['ref'])
                ->value('id');
        }

        $user = User::create([
            'name' => $input['name'],
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => $input['password'],
            'referrer_id' => $referrerId,
            'balance' => 0,
        ]);

        $this->binaryTreeService->placeUser($user, $user->referrer);

        return $user;
    }
}
