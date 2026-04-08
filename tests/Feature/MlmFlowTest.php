<?php

use App\Models\MlmBinaryLedger;
use App\Models\MlmPlan;
use App\Models\MlmInvoice;
use App\Models\MlmSubscription;
use App\Models\MlmTransaction;
use App\Models\MlmWithdrawalRequest;
use App\Models\User;
use App\Services\BinaryTreeService;

test('authenticated users can open mlm pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('mlm.network'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('mlm.plans.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('mlm.earnings'))
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
    ]);

    expect($root->fresh()->balance)->toBe('300.00');
});

test('members can submit withdrawal requests and admins can access admin modules', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $member = User::factory()->create([
        'balance' => 900,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.withdrawals.store'), [
            'amount' => 500,
            'payment_method' => 'bKash',
            'account_details' => '01710000000',
            'note' => 'Payout request',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('mlm_withdrawal_requests', [
        'user_id' => $member->id,
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

    $this->actingAs($member)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
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

    $this->actingAs($member)
        ->post(route('mlm.withdrawals.store'), [
            'amount' => 250,
            'payment_method' => 'Nagad',
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
