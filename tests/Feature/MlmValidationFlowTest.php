<?php

use App\Models\MlmPaymentMethod;
use App\Models\User;

test('wallet transfer cannot be sent to the same account', function () {
    $user = User::factory()->create([
        'balance' => 200,
    ]);

    $this->actingAs($user)
        ->post(route('mlm.wallet.transfer'), [
            'receiver_identity' => $user->member_code,
            'amount' => 20,
            'note' => 'Self transfer',
        ])
        ->assertSessionHasErrors(['receiver_identity']);
});

test('wallet transfer checks total debit including fee', function () {
    $sender = User::factory()->create([
        'balance' => 50,
    ]);

    $receiver = User::factory()->create([
        'balance' => 0,
    ]);

    $this->actingAs($sender)
        ->post(route('mlm.wallet.transfer'), [
            'receiver_identity' => $receiver->member_code,
            'amount' => 50,
        ])
        ->assertSessionHasErrors(['amount']);
});

test('withdrawal request can not exceed available balance', function () {
    $member = User::factory()->create([
        'balance' => 100,
    ]);

    $payoutMethod = paymentMethod([
        'name' => 'Bank Transfer',
        'code' => 'BANK_LIMIT',
        'type' => MlmPaymentMethod::TYPE_BANK,
        'supports_deposit' => false,
        'supports_withdrawal' => true,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.withdrawals.store'), [
            'amount' => 150,
            'payment_method_id' => $payoutMethod->id,
            'account_details' => '1234567890',
        ])
        ->assertSessionHasErrors(['amount']);
});

test('deposit method must match the member country coverage', function () {
    $member = User::factory()->create([
        'country_code' => 'BD',
    ]);

    $aeMethod = paymentMethod([
        'name' => 'UAE Bank',
        'code' => 'UAE_BANK',
        'type' => MlmPaymentMethod::TYPE_BANK,
        'country_code' => 'AE',
        'supports_deposit' => true,
        'supports_withdrawal' => false,
    ]);

    $this->actingAs($member)
        ->post(route('mlm.payments.store'), [
            'payment_method_id' => $aeMethod->id,
            'amount' => 100,
            'transaction_reference' => 'AE-REF-100',
        ])
        ->assertSessionHasErrors(['payment_method_id']);
});
