<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $username = fake()->unique()->userName();

        return [
            'name' => fake()->name(),
            'username' => Str::lower($username),
            'member_code' => 'PGX-'.Str::upper(fake()->unique()->bothify('####???')),
            'email' => fake()->unique()->safeEmail(),
            'country_code' => 'BD',
            'phone_code' => '+880',
            'phone_number' => fake()->numerify('17########'),
            'city' => fake()->city(),
            'profession' => fake()->jobTitle(),
            'company_name' => fake()->company(),
            'profile_headline' => fake()->sentence(4),
            'bio' => fake()->paragraph(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'referrer_id' => null,
            'binary_parent_id' => null,
            'binary_position' => null,
            'balance' => fake()->randomFloat(2, 0, 5000),
            'is_admin' => false,
            'current_rank_id' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
