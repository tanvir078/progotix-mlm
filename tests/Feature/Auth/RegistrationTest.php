<?php

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $sponsor = User::factory()->create([
        'username' => 'sponsor001',
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'username' => 'johndoe',
        'email' => 'test@example.com',
        'ref' => $sponsor->username,
        'country_code' => 'BD',
        'phone_code' => '+880',
        'phone_number' => '1712345678',
        'city' => 'Dhaka',
        'profession' => 'Retail Partner',
        'company_name' => 'Growth Desk',
        'profile_headline' => 'Independent product seller',
        'bio' => 'Focused on customer-first growth.',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'username' => 'johndoe',
        'country_code' => 'BD',
        'phone_number' => '1712345678',
        'referrer_id' => $sponsor->id,
    ]);
});
