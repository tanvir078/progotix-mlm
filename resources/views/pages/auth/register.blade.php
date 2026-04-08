<?php

use App\Models\User;
use App\Services\BinaryTreeService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $ref = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        // লিংকে যদি ?ref=admin থাকে তবে সেটি অটোমেটিক বক্সে বসে যাবে
        $this->ref = request()->query('ref', '');
    }

    public function register(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'alpha_dash', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'ref' => ['nullable', 'string', 'exists:users,username'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $referrer = null;
        if ($this->ref) {
            $referrer = User::where('username', $this->ref)->first();
        }

        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'referrer_id' => $referrer ? $referrer->id : null,
            'balance' => 0,
        ]);

        app(BinaryTreeService::class)->placeUser($user, $referrer);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form wire:submit="register" class="flex flex-col gap-6">
            @csrf

            <flux:input
                wire:model="name"
                :label="__('Full Name')"
                type="text"
                required
                autofocus
                :placeholder="__('Enter your full name')"
            />

            <flux:input
                wire:model="username"
                :label="__('Username')"
                type="text"
                required
                :placeholder="__('Choose a unique username')"
            />

            <flux:input
                wire:model="email"
                :label="__('Email address')"
                type="email"
                required
                placeholder="email@example.com"
            />

            <flux:input
                wire:model="ref"
                :label="__('Referral Username (Optional)')"
                type="text"
                :placeholder="__('Enter referral username')"
            />

            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                :placeholder="__('Password')"
                viewable
            />

            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                :placeholder="__('Confirm password')"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
