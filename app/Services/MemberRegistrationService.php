<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class MemberRegistrationService
{
    public function __construct(
        private readonly BinaryTreeService $binaryTreeService,
        private readonly RankService $rankService,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function registerUnderSponsor(
        array $attributes,
        User $sponsor,
        string $placementPreference = 'auto',
        bool $markVerified = false,
    ): User {
        return DB::transaction(function () use ($attributes, $sponsor, $placementPreference, $markVerified): User {
            $country = collect(config('countries.list'))
                ->firstWhere('code', $attributes['country_code']);

            $user = User::query()->create([
                'name' => $attributes['name'],
                'username' => strtolower((string) $attributes['username']),
                'email' => strtolower((string) $attributes['email']),
                'country_code' => $attributes['country_code'],
                'phone_code' => $country['dial_code'] ?? $attributes['phone_code'] ?? null,
                'phone_number' => $attributes['phone_number'],
                'city' => $attributes['city'] ?? null,
                'profession' => $attributes['profession'] ?? null,
                'company_name' => $attributes['company_name'] ?? null,
                'profile_headline' => $attributes['profile_headline'] ?? null,
                'bio' => $attributes['bio'] ?? null,
                'password' => $attributes['password'],
                'referrer_id' => $sponsor->id,
                'balance' => 0,
            ]);

            if ($markVerified) {
                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            }

            $this->binaryTreeService->placeUser($user, $sponsor, $placementPreference);
            $this->rankService->sync($sponsor->fresh());

            return $user->fresh(['referrer', 'binaryParent']);
        });
    }
}
