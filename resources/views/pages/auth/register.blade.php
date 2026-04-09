<?php

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Services\MemberRegistrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component
{
    use ProfileValidationRules;

    public array $countries = [];
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $ref = '';
    public string $country_code = 'BD';
    public string $phone_code = '+880';
    public string $phone_number = '';
    public string $city = '';
    public string $profession = '';
    public string $company_name = '';
    public string $profile_headline = '';
    public string $bio = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        $this->countries = config('countries.list');
        $this->ref = request()->query('ref', '');
        $this->syncPhoneCode();
    }

    public function updatedCountryCode(): void
    {
        $this->syncPhoneCode();
    }

    private function syncPhoneCode(): void
    {
        $country = collect($this->countries)->firstWhere('code', $this->country_code);

        if ($country) {
            $this->phone_code = $country['dial_code'];
        }
    }

    public function register(): void
    {
        $this->validate([
            ...$this->profileRules(),
            'username' => ['required', 'string', 'lowercase', 'alpha_dash', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'ref' => ['required', 'string', 'exists:users,username'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $referrer = User::where('username', $this->ref)->first();

        $user = app(MemberRegistrationService::class)->registerUnderSponsor([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'country_code' => $this->country_code,
            'phone_code' => $this->phone_code,
            'phone_number' => $this->phone_number,
            'city' => $this->city,
            'profession' => $this->profession,
            'company_name' => $this->company_name,
            'profile_headline' => $this->profile_headline,
            'bio' => $this->bio,
            'password' => $this->password,
        ], $referrer);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Join through a sponsor code and complete your professional member profile')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form wire:submit="register" class="flex flex-col gap-6">
            @csrf

            <div class="rounded-[1.75rem] border border-zinc-200/80 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/70">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="app-brand-badge">Referral Required</p>
                        <h2 class="mt-3 text-lg font-semibold text-zinc-950 dark:text-white">Sponsor & account identity</h2>
                    </div>
                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Step 1</p>
                </div>

                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <flux:input wire:model="ref" :label="__('Referral Code / Username')" type="text" required :placeholder="__('Enter sponsor username')" />
                    <flux:input wire:model="username" :label="__('Your Referral Code')" type="text" required :placeholder="__('Choose your username')" />
                    <flux:input wire:model="name" :label="__('Full Name')" type="text" required autofocus :placeholder="__('Enter your full name')" />
                    <flux:input wire:model="email" :label="__('Email address')" type="email" required placeholder="email@example.com" />
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-zinc-200/80 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/70">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Country & contact</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Worldwide country selection with phone dial code alignment</p>
                    </div>
                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Step 2</p>
                </div>

                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div class="grid gap-2">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Country') }}</label>
                        <select wire:model.live="country_code" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-xs outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:ring-teal-900">
                            @foreach (config('countries.list') as $country)
                                <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                        @error('country_code') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <flux:input wire:model="phone_code" :label="__('Phone Code')" type="text" readonly />
                    <flux:input wire:model="phone_number" :label="__('Phone Number')" type="text" required :placeholder="__('Enter your phone number')" />
                    <flux:input wire:model="city" :label="__('City')" type="text" :placeholder="__('City or region')" />
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-zinc-200/80 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/70">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Professional profile</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Set up your public member identity for team trust and communication</p>
                    </div>
                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Step 3</p>
                </div>

                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <flux:input wire:model="profession" :label="__('Profession')" type="text" :placeholder="__('Sales leader, consultant, retailer...')" />
                    <flux:input wire:model="company_name" :label="__('Company / Team Name')" type="text" :placeholder="__('Your company or team brand')" />
                    <div class="md:col-span-2">
                        <flux:input wire:model="profile_headline" :label="__('Profile Headline')" type="text" :placeholder="__('Short professional summary for your member profile')" />
                    </div>
                    <div class="grid gap-2 md:col-span-2">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Bio') }}</label>
                        <textarea wire:model="bio" rows="4" class="w-full rounded-[1.4rem] border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-xs outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:ring-teal-900" placeholder="Introduce yourself, your market focus, and how you support customers or downline members."></textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-zinc-200/80 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/70">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Security</h2>
                    </div>
                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Step 4</p>
                </div>

                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <flux:input wire:model="password" :label="__('Password')" type="password" required :placeholder="__('Password')" viewable />
                    <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" required :placeholder="__('Confirm password')" viewable />
                </div>
            </div>

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
