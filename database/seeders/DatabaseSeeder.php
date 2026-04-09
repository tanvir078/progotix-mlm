<?php

namespace Database\Seeders;

use App\Models\MlmDepositRequest;
use App\Models\MlmInvoice;
use App\Models\MlmPaymentMethod;
use App\Models\MlmPlan;
use App\Models\MlmProduct;
use App\Models\MlmRank;
use App\Models\MlmWithdrawalRequest;
use App\Models\User;
use App\Services\BinaryTreeService;
use App\Services\MlmActivationService;
use App\Services\OrderService;
use App\Services\RankService;
use App\Services\WalletTransferService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $paymentMethods = collect([
            [
                'name' => 'bKash Personal',
                'code' => 'BKASH_BD',
                'type' => MlmPaymentMethod::TYPE_E_WALLET,
                'country_code' => 'BD',
                'currency_code' => 'BDT',
                'provider_name' => 'bKash',
                'destination_label' => 'Wallet Number',
                'destination_value' => '01700000000',
                'instructions' => 'Send money and submit the transaction ID for admin verification.',
                'min_amount' => 100,
                'max_amount' => 50000,
                'fixed_charge' => 0,
                'percent_charge_rate' => 0.01,
                'supports_deposit' => true,
                'supports_withdrawal' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Nagad',
                'code' => 'NAGAD_BD',
                'type' => MlmPaymentMethod::TYPE_E_WALLET,
                'country_code' => 'BD',
                'currency_code' => 'BDT',
                'provider_name' => 'Nagad',
                'destination_label' => 'Wallet Number',
                'destination_value' => '01800000000',
                'instructions' => 'Submit the payment reference after funding from your Nagad account.',
                'min_amount' => 100,
                'max_amount' => 50000,
                'fixed_charge' => 0,
                'percent_charge_rate' => 0.01,
                'supports_deposit' => true,
                'supports_withdrawal' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Stripe Card Review',
                'code' => 'STRIPE_CARD',
                'type' => MlmPaymentMethod::TYPE_CARD,
                'country_code' => null,
                'currency_code' => 'USD',
                'provider_name' => 'Stripe',
                'destination_label' => 'Processor',
                'destination_value' => 'Hosted card link',
                'instructions' => 'Use the card processor reference as your transaction ID.',
                'min_amount' => 25,
                'max_amount' => 5000,
                'fixed_charge' => 1.5,
                'percent_charge_rate' => 0.029,
                'supports_deposit' => true,
                'supports_withdrawal' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'USDT TRC20',
                'code' => 'USDT_TRC20',
                'type' => MlmPaymentMethod::TYPE_CRYPTO,
                'country_code' => null,
                'currency_code' => 'USDT',
                'provider_name' => 'Binance / External Wallet',
                'destination_label' => 'Wallet Address',
                'destination_value' => 'TRC20-TREASURY-ADDRESS',
                'instructions' => 'Only send TRC20 USDT and submit the hash for review.',
                'min_amount' => 25,
                'max_amount' => 25000,
                'fixed_charge' => 0,
                'percent_charge_rate' => 0.005,
                'supports_deposit' => true,
                'supports_withdrawal' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'UAE Bank Transfer',
                'code' => 'UAE_BANK',
                'type' => MlmPaymentMethod::TYPE_BANK,
                'country_code' => 'AE',
                'currency_code' => 'AED',
                'provider_name' => 'Mashreq',
                'destination_label' => 'IBAN',
                'destination_value' => 'AE070331234567890123456',
                'instructions' => 'Mention member code in the transfer reference before submission.',
                'min_amount' => 100,
                'max_amount' => 50000,
                'fixed_charge' => 5,
                'percent_charge_rate' => 0,
                'supports_deposit' => true,
                'supports_withdrawal' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ])->map(fn (array $method) => MlmPaymentMethod::query()->updateOrCreate(
            ['code' => $method['code']],
            $method
        ));

        $products = collect(config('mlm.commerce.products'))
            ->map(function (array $product) {
                return MlmProduct::query()->updateOrCreate(
                    ['sku' => $product['sku']],
                    [
                        'name' => $product['name'],
                        'slug' => Str::slug($product['name']),
                        'category' => $product['category'],
                        'description' => $product['description'],
                        'price' => $product['price'],
                        'bv' => $product['bv'],
                        'retail_commission_rate' => $product['retail_commission_rate'],
                        'team_bonus_rate' => 0.03,
                        'sort_order' => $product['team_volume'],
                        'is_active' => true,
                    ]
                );
            });

        collect([
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'badge_color' => 'zinc',
                'direct_referrals_required' => 0,
                'personal_sales_required' => 0,
                'team_volume_required' => 0,
                'bonus_amount' => 0,
                'sort_order' => 1,
            ],
            [
                'name' => 'Bronze Builder',
                'slug' => 'bronze-builder',
                'badge_color' => 'amber',
                'direct_referrals_required' => 1,
                'personal_sales_required' => 1000,
                'team_volume_required' => 200,
                'bonus_amount' => 25,
                'sort_order' => 2,
            ],
            [
                'name' => 'Silver Leader',
                'slug' => 'silver-leader',
                'badge_color' => 'slate',
                'direct_referrals_required' => 2,
                'personal_sales_required' => 2500,
                'team_volume_required' => 600,
                'bonus_amount' => 75,
                'sort_order' => 3,
            ],
            [
                'name' => 'Gold Director',
                'slug' => 'gold-director',
                'badge_color' => 'yellow',
                'direct_referrals_required' => 3,
                'personal_sales_required' => 4500,
                'team_volume_required' => 1200,
                'bonus_amount' => 150,
                'sort_order' => 4,
            ],
        ])->each(fn (array $rank) => MlmRank::query()->updateOrCreate(
            ['slug' => $rank['slug']],
            $rank
        ));

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
            'country_code' => 'BD',
            'phone_code' => '+880',
            'phone_number' => '1710000000',
            'city' => 'Dhaka',
            'profession' => 'Founder',
            'company_name' => 'ProgotiX Global',
            'profile_headline' => 'Global sponsor and system operator',
            'bio' => 'Leads the admin side of the MLM command center and onboarding flow.',
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
            'country_code' => 'BD',
            'phone_code' => '+880',
            'phone_number' => '1812345678',
            'city' => 'Dhaka',
            'profession' => 'Sales Partner',
            'company_name' => 'Retail Circle',
            'profile_headline' => 'Community seller and team builder',
            'bio' => 'Focused on direct customer sales and mentoring active referrals.',
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
            'country_code' => 'AE',
            'phone_code' => '+971',
            'phone_number' => '501234567',
            'city' => 'Dubai',
            'profession' => 'Regional Leader',
            'company_name' => 'Growth Hub',
            'profile_headline' => 'Regional retail team manager',
            'bio' => 'Handles premium customer accounts and fast-growing referral legs.',
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
            'country_code' => 'IN',
            'phone_code' => '+91',
            'phone_number' => '9876543210',
            'city' => 'Kolkata',
            'profession' => 'Field Associate',
            'company_name' => 'Starter Network',
            'profile_headline' => 'Emerging builder in the east zone',
            'bio' => 'Starting with packages, customer demos, and local market outreach.',
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
        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);
        /** @var WalletTransferService $walletTransferService */
        $walletTransferService = app(WalletTransferService::class);
        /** @var RankService $rankService */
        $rankService = app(RankService::class);

        if (! $member->subscriptions()->exists()) {
            $activationService->activate($member, $plans[1]);
        }

        if (! $salesLead->subscriptions()->exists()) {
            $activationService->activate($salesLead, $plans[0]);
        }

        if (! $junior->subscriptions()->exists()) {
            $activationService->activate($junior, $plans[2]);
        }

        if (! $member->orders()->exists()) {
            $orderService->place($member, $products[0], 2);
            $orderService->place($member, $products[1], 1);
        }

        if (! $salesLead->orders()->exists()) {
            $orderService->place($salesLead, $products[2], 1);
        }

        if (! $junior->orders()->exists()) {
            $orderService->place($junior, $products[3], 1);
        }

        if (! $member->walletTransfersSent()->exists()) {
            $walletTransferService->transfer($member->fresh(), $junior->fresh(), 40, 'Starter team support transfer');
        }

        $member->documents()->updateOrCreate([
            'document_type' => 'National ID',
        ], [
            'document_number' => 'NID-00998877',
            'country_code' => 'BD',
            'status' => 'pending',
            'notes' => 'Sample optional KYC document.',
            'submitted_at' => now(),
        ]);

        $rankService->sync($member->fresh());
        $rankService->sync($salesLead->fresh());
        $rankService->sync($junior->fresh());

        $member->refresh();

        MlmWithdrawalRequest::query()->updateOrCreate([
            'user_id' => $member->id,
            'status' => MlmWithdrawalRequest::STATUS_PENDING,
        ], [
            'payment_method_id' => $paymentMethods->firstWhere('code', 'BKASH_BD')?->id,
            'amount' => 500,
            'payment_method' => 'bKash',
            'account_details' => '01700000000',
            'note' => 'Sample pending payout request.',
        ]);

        MlmDepositRequest::query()->updateOrCreate([
            'user_id' => $member->id,
            'status' => MlmDepositRequest::STATUS_PENDING,
        ], [
            'payment_method_id' => $paymentMethods->firstWhere('code', 'USDT_TRC20')?->id,
            'payment_method_name' => 'USDT TRC20',
            'payment_method_type' => MlmPaymentMethod::TYPE_CRYPTO,
            'payment_method_snapshot' => [
                'name' => 'USDT TRC20',
                'currency_code' => 'USDT',
            ],
            'currency' => 'USDT',
            'amount' => 250,
            'charge_amount' => 1.25,
            'net_amount' => 248.75,
            'sender_name' => 'Sample Wallet',
            'sender_account' => 'TRC20-SAMPLE-WALLET',
            'transaction_reference' => 'SEED-DEPOSIT-1001',
            'note' => 'Sample pending deposit request.',
            'submitted_at' => now(),
        ]);

        MlmInvoice::query()
            ->where('user_id', $admin->id)
            ->delete();
    }
}
