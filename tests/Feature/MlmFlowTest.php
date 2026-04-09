<?php

use App\Models\MlmBinaryLedger;
use App\Models\MlmDepositRequest;
use App\Models\MlmDocument;
use App\Models\MlmInvoice;
use App\Models\MlmOrder;
use App\Models\MlmPaymentMethod;
use App\Models\MlmPlan;
use App\Models\MlmProduct;
use App\Models\MlmRank;
use App\Models\MlmSubscription;
use App\Models\MlmTransaction;
use App\Models\MlmWithdrawalRequest;
use App\Models\User;
use App\Services\BinaryTreeService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('authenticated users can open mlm pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('mlm.network'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('mlm.plans.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('mlm.shop'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('mlm.earnings'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('mlm.orders'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('mlm.wallet'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('mlm.payments.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('mlm.ranks'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('mlm.documents'))
        ->assertOk();
});

test('activating a package credits direct sponsor commission', function () {
    $sponsor = User::factory()->create([
        'balance' => 0,
    ]);

    $member = User::factory()->create([
        'referrer_id' => $sponsor->id,
        'balance' => 0,
    ]);

    $plan = MlmPlan::create([
        'name' => 'Starter',
        'code' => 'STARTER',
        'description' => 'Starter package',
        'price' => 1500,
        'direct_bonus' => 200,
        'level_bonus' => 50,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.plans.subscribe', $plan))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('mlm_subscriptions', [
        'user_id' => $member->id,
        'plan_id' => $plan->id,
        'status' => MlmSubscription::STATUS_ACTIVE,
    ]);

    $this->assertDatabaseHas('mlm_transactions', [
        'user_id' => $sponsor->id,
        'source_user_id' => $member->id,
        'type' => MlmTransaction::TYPE_DIRECT_BONUS,
        'amount' => '200.00',
    ]);

    $this->assertDatabaseHas('mlm_invoices', [
        'user_id' => $member->id,
        'subscription_id' => MlmSubscription::query()->value('id'),
        'status' => MlmInvoice::STATUS_PAID,
    ]);

    expect($sponsor->fresh()->balance)->toBe('200.00');
});

test('activating a package credits level commission to higher upline', function () {
    $root = User::factory()->create([
        'balance' => 0,
    ]);

    $sponsor = User::factory()->create([
        'referrer_id' => $root->id,
        'balance' => 0,
    ]);

    $member = User::factory()->create([
        'referrer_id' => $sponsor->id,
        'balance' => 0,
    ]);

    $plan = MlmPlan::create([
        'name' => 'Leader',
        'code' => 'LEADER',
        'description' => 'Leader package',
        'price' => 12000,
        'direct_bonus' => 1800,
        'level_bonus' => 600,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.plans.subscribe', $plan))
        ->assertRedirect();

    $this->assertDatabaseHas('mlm_transactions', [
        'user_id' => $root->id,
        'source_user_id' => $member->id,
        'type' => MlmTransaction::TYPE_LEVEL_BONUS,
        'amount' => '300.00',
        'commission_level' => 1,
    ]);

    expect($root->fresh()->balance)->toBe('300.00');
});

test('activating a package distributes configured level commission across ten upline levels', function () {
    $chain = collect();
    $parent = null;

    foreach (range(1, 12) as $index) {
        $member = User::factory()->create([
            'balance' => 0,
            'referrer_id' => $parent?->id,
        ]);

        $chain->push($member);
        $parent = $member;
    }

    $activatingMember = $chain->last();
    $plan = MlmPlan::create([
        'name' => 'Global Leader',
        'code' => 'GLOBAL-LEADER',
        'description' => 'Ten level team plan',
        'price' => 20000,
        'direct_bonus' => 1000,
        'level_bonus' => 1000,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($activatingMember)
        ->post(route('mlm.plans.subscribe', $plan))
        ->assertRedirect();

    $distribution = config('mlm.commission.subscription.level_distribution');

    foreach ($distribution as $level => $ratio) {
        $earner = $chain->get(10 - $level);

        $this->assertDatabaseHas('mlm_transactions', [
            'user_id' => $earner->id,
            'source_user_id' => $activatingMember->id,
            'subscription_id' => MlmSubscription::query()->value('id'),
            'type' => MlmTransaction::TYPE_LEVEL_BONUS,
            'commission_level' => $level,
            'amount' => number_format(1000 * $ratio, 2, '.', ''),
        ]);
    }

    $this->assertDatabaseHas('mlm_transactions', [
        'user_id' => $chain->get(10)->id,
        'source_user_id' => $activatingMember->id,
        'type' => MlmTransaction::TYPE_DIRECT_BONUS,
        'amount' => '1000.00',
    ]);
});

test('members can submit withdrawal requests and admins can access admin modules', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $member = User::factory()->create([
        'balance' => 900,
    ]);

    $payoutMethod = paymentMethod([
        'name' => 'bKash',
        'code' => 'BKASH_BD',
        'country_code' => 'BD',
        'currency_code' => 'BDT',
        'supports_deposit' => false,
        'supports_withdrawal' => true,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.withdrawals.store'), [
            'amount' => 500,
            'payment_method_id' => $payoutMethod->id,
            'account_details' => '01710000000',
            'note' => 'Payout request',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('mlm_withdrawal_requests', [
        'user_id' => $member->id,
        'payment_method_id' => $payoutMethod->id,
        'payment_method' => 'bKash',
        'status' => MlmWithdrawalRequest::STATUS_PENDING,
        'amount' => '500.00',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.members'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.plans'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.payment-methods'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.deposits'))
        ->assertOk();

    $this->actingAs($member)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('members can submit deposit requests without immediate wallet credit', function () {
    $member = User::factory()->create([
        'balance' => 50,
    ]);

    $depositMethod = paymentMethod([
        'name' => 'USDT TRC20',
        'code' => 'USDT_TRC20',
        'type' => MlmPaymentMethod::TYPE_CRYPTO,
        'currency_code' => 'USDT',
        'fixed_charge' => 2,
        'percent_charge_rate' => 0.03,
        'supports_deposit' => true,
        'supports_withdrawal' => false,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.payments.store'), [
            'payment_method_id' => $depositMethod->id,
            'amount' => 100,
            'transaction_reference' => 'TX-100-ABC',
            'sender_name' => 'Member Wallet',
            'sender_account' => 'TRC20-ADDRESS',
        ])
        ->assertRedirect(route('mlm.payments.index'));

    $this->assertDatabaseHas('mlm_deposit_requests', [
        'user_id' => $member->id,
        'payment_method_id' => $depositMethod->id,
        'status' => MlmDepositRequest::STATUS_PENDING,
        'amount' => '100.00',
        'charge_amount' => '5.00',
        'net_amount' => '95.00',
    ]);

    expect($member->fresh()->balance)->toBe('50.00');
});

test('admins can approve deposit requests and credit net amount to the member wallet', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $member = User::factory()->create([
        'balance' => 10,
    ]);

    $depositMethod = paymentMethod([
        'name' => 'Stripe Card',
        'code' => 'STRIPE_CARD',
        'type' => MlmPaymentMethod::TYPE_CARD,
        'currency_code' => 'USD',
        'fixed_charge' => 2,
        'percent_charge_rate' => 0.03,
        'supports_deposit' => true,
        'supports_withdrawal' => false,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.payments.store'), [
            'payment_method_id' => $depositMethod->id,
            'amount' => 100,
            'transaction_reference' => 'CARD-REF-100',
            'sender_name' => 'Member Card',
        ]);

    $depositRequest = MlmDepositRequest::query()->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.deposits.update', $depositRequest), [
            'decision' => 'approve',
            'admin_note' => 'Card funding verified.',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('mlm_deposit_requests', [
        'id' => $depositRequest->id,
        'status' => MlmDepositRequest::STATUS_APPROVED,
        'processed_by' => $admin->id,
    ]);

    $this->assertDatabaseHas('mlm_transactions', [
        'user_id' => $member->id,
        'deposit_request_id' => $depositRequest->id,
        'type' => MlmTransaction::TYPE_DEPOSIT,
        'direction' => 'credit',
        'amount' => '95.00',
    ]);

    expect($member->fresh()->balance)->toBe('105.00');
});

test('admins can approve withdrawal requests through the locked service flow', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $member = User::factory()->create([
        'balance' => 900,
    ]);

    $payoutMethod = paymentMethod([
        'name' => 'bKash',
        'code' => 'BKASH_APPROVE',
        'country_code' => 'BD',
        'currency_code' => 'BDT',
        'supports_deposit' => false,
        'supports_withdrawal' => true,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.withdrawals.store'), [
            'amount' => 500,
            'payment_method_id' => $payoutMethod->id,
            'account_details' => '01710000000',
            'note' => 'Payout request',
        ]);

    $withdrawalRequest = MlmWithdrawalRequest::query()->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.withdrawals.update', $withdrawalRequest), [
            'decision' => 'approve',
            'admin_note' => 'Approved after ledger review.',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('mlm_withdrawal_requests', [
        'id' => $withdrawalRequest->id,
        'status' => MlmWithdrawalRequest::STATUS_APPROVED,
        'processed_by' => $admin->id,
    ]);

    $this->assertDatabaseHas('mlm_transactions', [
        'user_id' => $member->id,
        'type' => MlmTransaction::TYPE_WITHDRAWAL,
        'direction' => 'debit',
        'amount' => '500.00',
    ]);

    expect($member->fresh()->balance)->toBe('400.00');
});

test('admins can reject withdrawal requests without debiting the member wallet', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $member = User::factory()->create([
        'balance' => 900,
    ]);

    $payoutMethod = paymentMethod([
        'name' => 'Nagad',
        'code' => 'NAGAD_REJECT',
        'country_code' => 'BD',
        'currency_code' => 'BDT',
        'supports_deposit' => false,
        'supports_withdrawal' => true,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.withdrawals.store'), [
            'amount' => 500,
            'payment_method_id' => $payoutMethod->id,
            'account_details' => '01810000000',
            'note' => 'Payout request',
        ]);

    $withdrawalRequest = MlmWithdrawalRequest::query()->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.withdrawals.update', $withdrawalRequest), [
            'decision' => 'reject',
            'admin_note' => 'Rejected because verification is incomplete.',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('mlm_withdrawal_requests', [
        'id' => $withdrawalRequest->id,
        'status' => MlmWithdrawalRequest::STATUS_REJECTED,
        'processed_by' => $admin->id,
    ]);

    $this->assertDatabaseMissing('mlm_transactions', [
        'user_id' => $member->id,
        'type' => MlmTransaction::TYPE_WITHDRAWAL,
        'amount' => '500.00',
    ]);

    expect($member->fresh()->balance)->toBe('900.00');
});

test('binary volume creates carry forward and binary bonus', function () {
    $binaryTree = app(BinaryTreeService::class);

    $root = User::factory()->create([
        'balance' => 0,
    ]);

    $leftMember = User::factory()->create([
        'referrer_id' => $root->id,
        'balance' => 0,
    ]);

    $rightMember = User::factory()->create([
        'referrer_id' => $root->id,
        'balance' => 0,
    ]);

    $binaryTree->placeUser($leftMember, $root);
    $binaryTree->placeUser($rightMember, $root);

    $plan = MlmPlan::create([
        'name' => 'Growth',
        'code' => 'GROWTH',
        'description' => 'Growth package',
        'price' => 5000,
        'direct_bonus' => 0,
        'level_bonus' => 0,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($leftMember)
        ->post(route('mlm.plans.subscribe', $plan))
        ->assertRedirect();

    $this->actingAs($rightMember)
        ->post(route('mlm.plans.subscribe', $plan))
        ->assertRedirect();

    $ledger = MlmBinaryLedger::query()->where('user_id', $root->id)->first();

    expect($ledger)->not->toBeNull();
    expect($ledger->left_carry)->toBe('0.00');
    expect($ledger->right_carry)->toBe('0.00');
    expect($ledger->pair_volume)->toBe('5000.00');
    expect($root->fresh()->balance)->toBe('500.00');

    $this->assertDatabaseHas('mlm_transactions', [
        'user_id' => $root->id,
        'type' => MlmTransaction::TYPE_BINARY_BONUS,
        'amount' => '500.00',
    ]);
});

test('admins can manage plans and export payouts', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $member = User::factory()->create([
        'balance' => 1000,
    ]);

    $payoutMethod = paymentMethod([
        'name' => 'Nagad',
        'code' => 'NAGAD_EXPORT',
        'country_code' => 'BD',
        'currency_code' => 'BDT',
        'supports_deposit' => false,
        'supports_withdrawal' => true,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.withdrawals.store'), [
            'amount' => 250,
            'payment_method_id' => $payoutMethod->id,
            'account_details' => '01810000000',
            'note' => 'Export me',
        ]);

    $this->actingAs($admin)
        ->post(route('admin.plans.store'), [
            'name' => 'Starter',
            'code' => 'STARTER',
            'description' => 'Starter package',
            'price' => 1000,
            'direct_bonus' => 100,
            'level_bonus' => 50,
            'sort_order' => 1,
            'is_active' => '1',
        ])
        ->assertRedirect();

    $plan = MlmPlan::query()->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.plans.update', $plan), [
            'name' => 'Starter Plus',
            'code' => 'STARTER',
            'description' => 'Starter updated package',
            'price' => 1200,
            'direct_bonus' => 150,
            'level_bonus' => 60,
            'sort_order' => 2,
            'is_active' => '1',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('mlm_plans', [
        'name' => 'Starter Plus',
        'price' => '1200.00',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.withdrawals.export'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())->toContain('Nagad');
});

test('placing retail orders creates pending order and invoice without posting commissions', function () {
    $sponsor = User::factory()->create([
        'balance' => 0,
    ]);

    $member = User::factory()->create([
        'referrer_id' => $sponsor->id,
        'balance' => 0,
    ]);

    $product = MlmProduct::create([
        'sku' => 'PGX-TEST-1',
        'name' => 'Retail Box',
        'slug' => 'retail-box',
        'category' => 'wellness',
        'description' => 'Retail pack',
        'price' => 100,
        'bv' => 20,
        'retail_commission_rate' => 0.10,
        'team_bonus_rate' => 0.03,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.orders.store', $product), [
            'quantity' => 2,
        ])
        ->assertRedirect(route('mlm.orders'));

    $this->assertDatabaseHas('mlm_orders', [
        'user_id' => $member->id,
        'status' => MlmOrder::STATUS_PENDING,
        'subtotal' => '200.00',
    ]);

    $this->assertDatabaseHas('mlm_invoices', [
        'user_id' => $member->id,
        'status' => MlmInvoice::STATUS_PENDING,
        'amount' => '200.00',
    ]);

    $this->assertDatabaseMissing('mlm_transactions', [
        'user_id' => $member->id,
        'type' => MlmTransaction::TYPE_RETAIL_COMMISSION,
    ]);

    $this->assertDatabaseMissing('mlm_transactions', [
        'user_id' => $sponsor->id,
        'type' => MlmTransaction::TYPE_TEAM_SALES_BONUS,
    ]);

    expect($member->fresh()->balance)->toBe('0.00');
    expect($sponsor->fresh()->balance)->toBe('0.00');
});

test('admins can confirm retail order payments and trigger commissions', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $sponsor = User::factory()->create([
        'balance' => 0,
    ]);

    $member = User::factory()->create([
        'referrer_id' => $sponsor->id,
        'balance' => 0,
    ]);

    $product = MlmProduct::create([
        'sku' => 'PGX-TEST-2',
        'name' => 'Retail Box Pro',
        'slug' => 'retail-box-pro',
        'category' => 'wellness',
        'description' => 'Retail pack',
        'price' => 100,
        'bv' => 20,
        'retail_commission_rate' => 0.10,
        'team_bonus_rate' => 0.03,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.orders.store', $product), [
            'quantity' => 2,
        ]);

    $order = MlmOrder::query()->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.orders.update', $order), [
            'status' => MlmOrder::STATUS_PAID,
            'notes' => 'Manual payment verified.',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('mlm_orders', [
        'id' => $order->id,
        'status' => MlmOrder::STATUS_PAID,
        'notes' => 'Manual payment verified.',
    ]);

    $this->assertDatabaseHas('mlm_invoices', [
        'order_id' => $order->id,
        'status' => MlmInvoice::STATUS_PAID,
    ]);

    $this->assertDatabaseHas('mlm_transactions', [
        'user_id' => $member->id,
        'order_id' => $order->id,
        'type' => MlmTransaction::TYPE_RETAIL_COMMISSION,
        'amount' => '20.00',
    ]);

    $this->assertDatabaseHas('mlm_transactions', [
        'user_id' => $sponsor->id,
        'order_id' => $order->id,
        'type' => MlmTransaction::TYPE_TEAM_SALES_BONUS,
        'amount' => '6.00',
    ]);

    expect($member->fresh()->balance)->toBe('20.00');
    expect($sponsor->fresh()->balance)->toBe('6.00');
});

test('cancelling a paid retail order posts reversal entries', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $sponsor = User::factory()->create([
        'balance' => 0,
    ]);

    $member = User::factory()->create([
        'referrer_id' => $sponsor->id,
        'balance' => 0,
    ]);

    $product = MlmProduct::create([
        'sku' => 'PGX-TEST-3',
        'name' => 'Retail Box Elite',
        'slug' => 'retail-box-elite',
        'category' => 'wellness',
        'description' => 'Retail pack',
        'price' => 100,
        'bv' => 20,
        'retail_commission_rate' => 0.10,
        'team_bonus_rate' => 0.03,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.orders.store', $product), [
            'quantity' => 2,
        ]);

    $order = MlmOrder::query()->firstOrFail();

    $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
        'status' => MlmOrder::STATUS_PAID,
        'notes' => 'Paid from bank transfer.',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.update', $order), [
            'status' => MlmOrder::STATUS_CANCELLED,
            'notes' => 'Refund approved after cancellation.',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('mlm_orders', [
        'id' => $order->id,
        'status' => MlmOrder::STATUS_CANCELLED,
        'notes' => 'Refund approved after cancellation.',
    ]);

    $this->assertDatabaseHas('mlm_invoices', [
        'order_id' => $order->id,
        'status' => MlmInvoice::STATUS_CANCELLED,
    ]);

    $this->assertDatabaseHas('mlm_transactions', [
        'user_id' => $member->id,
        'order_id' => $order->id,
        'type' => MlmTransaction::TYPE_RETAIL_COMMISSION_REVERSAL,
        'amount' => '20.00',
        'direction' => 'debit',
    ]);

    $this->assertDatabaseHas('mlm_transactions', [
        'user_id' => $sponsor->id,
        'order_id' => $order->id,
        'type' => MlmTransaction::TYPE_TEAM_SALES_BONUS_REVERSAL,
        'amount' => '6.00',
        'direction' => 'debit',
    ]);

    expect($member->fresh()->balance)->toBe('0.00');
    expect($sponsor->fresh()->balance)->toBe('0.00');
});

test('reinstating a cancelled paid order creates a new commission cycle', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $sponsor = User::factory()->create([
        'balance' => 0,
    ]);

    $member = User::factory()->create([
        'referrer_id' => $sponsor->id,
        'balance' => 0,
    ]);

    $product = MlmProduct::create([
        'sku' => 'PGX-TEST-4',
        'name' => 'Retail Box Restore',
        'slug' => 'retail-box-restore',
        'category' => 'wellness',
        'description' => 'Retail pack',
        'price' => 100,
        'bv' => 20,
        'retail_commission_rate' => 0.10,
        'team_bonus_rate' => 0.03,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.orders.store', $product), [
            'quantity' => 2,
        ]);

    $order = MlmOrder::query()->firstOrFail();

    $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
        'status' => MlmOrder::STATUS_PAID,
        'notes' => 'First confirmation.',
    ]);

    $this->actingAs($admin)->patch(route('admin.orders.update', $order), [
        'status' => MlmOrder::STATUS_CANCELLED,
        'notes' => 'Cancelled once.',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.update', $order), [
            'status' => MlmOrder::STATUS_PAID,
            'notes' => 'Reconfirmed after review.',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($order->fresh()->commission_cycle)->toBe(2);
    expect($member->fresh()->balance)->toBe('20.00');
    expect($sponsor->fresh()->balance)->toBe('6.00');

    expect(MlmTransaction::query()
        ->where('order_id', $order->id)
        ->where('type', MlmTransaction::TYPE_RETAIL_COMMISSION)
        ->count())->toBe(2);

    expect(MlmTransaction::query()
        ->where('order_id', $order->id)
        ->where('type', MlmTransaction::TYPE_TEAM_SALES_BONUS)
        ->count())->toBe(2);

    expect(MlmTransaction::query()
        ->where('order_id', $order->id)
        ->where('type', MlmTransaction::TYPE_RETAIL_COMMISSION_REVERSAL)
        ->count())->toBe(1);
});

test('wallet transfers debit sender and credit receiver by member code with fee', function () {
    $sender = User::factory()->create([
        'balance' => 200,
    ]);

    $receiver = User::factory()->create([
        'balance' => 50,
    ]);

    $this->actingAs($sender)
        ->post(route('mlm.wallet.transfer'), [
            'receiver_identity' => $receiver->member_code,
            'amount' => 50,
            'note' => 'Support transfer',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('mlm_wallet_transfers', [
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'amount' => '50.00',
        'fee' => '1.00',
        'net_amount' => '49.00',
    ]);

    expect($sender->fresh()->balance)->toBe('149.00');
    expect($receiver->fresh()->balance)->toBe('99.00');
});

test('sponsors can open downline ids with preferred leg placement', function () {
    $sponsor = User::factory()->create();

    $existingLeftMember = User::factory()->create([
        'referrer_id' => $sponsor->id,
    ]);

    app(BinaryTreeService::class)->placeUser($existingLeftMember, $sponsor, User::BINARY_LEFT);

    $this->actingAs($sponsor)
        ->post(route('mlm.network.downlines.store'), [
            'name' => 'Downline Member',
            'username' => 'teamstarter',
            'email' => 'teamstarter@example.com',
            'country_code' => 'BD',
            'phone_number' => '01712345678',
            'city' => 'Dhaka',
            'profession' => 'Team Builder',
            'company_name' => 'Growth Unit',
            'profile_headline' => 'Focused on left-leg expansion',
            'bio' => 'Member created by sponsor.',
            'placement_preference' => User::BINARY_LEFT,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $member = User::query()->where('username', 'teamstarter')->firstOrFail();

    expect($member->referrer_id)->toBe($sponsor->id);
    expect($member->binary_parent_id)->toBe($existingLeftMember->id);
    expect($member->binary_position)->toBe(User::BINARY_LEFT);
    expect($member->email_verified_at)->not->toBeNull();
});

test('documents can be submitted optionally with a file', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('mlm.documents.store'), [
            'document_type' => 'Passport',
            'document_number' => 'P1234567',
            'country_code' => 'BD',
            'document_file' => UploadedFile::fake()->image('passport.jpg'),
            'notes' => 'Verification file',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $document = MlmDocument::query()->firstOrFail();

    expect($document->document_type)->toBe('Passport');
    expect($document->file_path)->not->toBeNull();

    Storage::disk('public')->assertExists($document->file_path);
});

test('rank page reflects seeded or earned ranks', function () {
    MlmRank::create([
        'name' => 'Bronze',
        'slug' => 'bronze',
        'badge_color' => 'amber',
        'direct_referrals_required' => 0,
        'personal_sales_required' => 0,
        'team_volume_required' => 0,
        'bonus_amount' => 0,
        'sort_order' => 1,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('mlm.ranks'))
        ->assertOk()
        ->assertSee('Rank Ladder');
});

test('members can download invoice pdf', function () {
    $member = User::factory()->create();

    $plan = MlmPlan::create([
        'name' => 'Invoice Plan',
        'code' => 'INVOICE',
        'description' => 'Invoice test plan',
        'price' => 2000,
        'direct_bonus' => 0,
        'level_bonus' => 0,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.plans.subscribe', $plan))
        ->assertRedirect();

    $invoice = MlmInvoice::query()->where('user_id', $member->id)->firstOrFail();

    $response = $this->actingAs($member)
        ->get(route('mlm.invoices.pdf', $invoice));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect(substr($response->getContent(), 0, 4))->toBe('%PDF');
});
