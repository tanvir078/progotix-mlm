<?php

namespace Database\Seeders;

use App\Models\MlmInvoice;
use App\Models\MlmPlan;
use App\Models\MlmWithdrawalRequest;
use App\Models\User;
use App\Services\BinaryTreeService;
use App\Services\MlmActivationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $plans = collect([
            [
                'name' => 'Starter',
                'code' => 'STARTER',
                'description' => 'Entry package for new members with a modest direct bonus.',
                'price' => 1500,
                'direct_bonus' => 200,
                'level_bonus' => 75,
                'sort_order' => 1,
            ],
            [
                'name' => 'Growth',
                'code' => 'GROWTH',
                'description' => 'Balanced package for building an active team.',
                'price' => 5000,
                'direct_bonus' => 750,
                'level_bonus' => 250,
                'sort_order' => 2,
            ],
            [
                'name' => 'Leader',
                'code' => 'LEADER',
                'description' => 'Advanced package for high-performing network leaders.',
                'price' => 12000,
                'direct_bonus' => 1800,
                'level_bonus' => 600,
                'sort_order' => 3,
            ],
        ])->map(fn (array $plan) => MlmPlan::query()->updateOrCreate(
            ['code' => $plan['code']],
            $plan
        ));

        $admin = User::query()->updateOrCreate([
            'email' => 'admin@progotix.test',
        ], [
            'name' => 'Admin Sponsor',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'balance' => 0,
            'is_admin' => true,
        ]);

        $member = User::query()->updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'referrer_id' => $admin->id,
            'balance' => 0,
        ]);

        $salesLead = User::query()->updateOrCreate([
            'email' => 'sales@progotix.test',
        ], [
            'name' => 'Sales Lead',
            'username' => 'saleslead',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'referrer_id' => $admin->id,
            'balance' => 0,
        ]);

        $junior = User::query()->updateOrCreate([
            'email' => 'junior@progotix.test',
        ], [
            'name' => 'Junior Builder',
            'username' => 'juniorbuilder',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'referrer_id' => $member->id,
            'balance' => 0,
        ]);

        /** @var BinaryTreeService $binaryTreeService */
        $binaryTreeService = app(BinaryTreeService::class);
        $binaryTreeService->placeUser($member, $admin);
        $binaryTreeService->placeUser($salesLead, $admin);
        $binaryTreeService->placeUser($junior, $member);

        /** @var MlmActivationService $activationService */
        $activationService = app(MlmActivationService::class);

        if (! $member->subscriptions()->exists()) {
            $activationService->activate($member, $plans[1]);
        }

        if (! $salesLead->subscriptions()->exists()) {
            $activationService->activate($salesLead, $plans[0]);
        }

        if (! $junior->subscriptions()->exists()) {
            $activationService->activate($junior, $plans[2]);
        }

        $member->refresh();

        MlmWithdrawalRequest::query()->updateOrCreate([
            'user_id' => $member->id,
            'status' => MlmWithdrawalRequest::STATUS_PENDING,
        ], [
            'amount' => 500,
            'payment_method' => 'bKash',
            'account_details' => '01700000000',
            'note' => 'Sample pending payout request.',
        ]);

        MlmInvoice::query()
            ->where('user_id', $admin->id)
            ->delete();
    }
}
