<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public array $countries = [];
    public string $name = '';
    public string $email = '';
    public string $country_code = 'BD';
    public string $phone_code = '+880';
    public string $phone_number = '';
    public string $city = '';
    public string $profession = '';
    public string $company_name = '';
    public string $profile_headline = '';
    public string $bio = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->countries = config('countries.list');
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->country_code = Auth::user()->country_code ?? 'BD';
        $this->phone_code = Auth::user()->phone_code ?? '+880';
        $this->phone_number = Auth::user()->phone_number ?? '';
        $this->city = Auth::user()->city ?? '';
        $this->profession = Auth::user()->profession ?? '';
        $this->company_name = Auth::user()->company_name ?? '';
        $this->profile_headline = Auth::user()->profile_headline ?? '';
        $this->bio = Auth::user()->bio ?? '';
    }

    public function updatedCountryCode(): void
    {
        $country = collect($this->countries)->firstWhere('code', $this->country_code);

        if ($country) {
            $this->phone_code = $country['dial_code'];
        }
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Flux::toast(text: __('A new verification link has been sent to your email address.'));
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your professional member profile, country, phone, and public identity')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                <div class="rounded-[1.75rem] border border-zinc-200/80 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/70">
                    <p class="app-brand-badge">{{ __('Member Identity') }}</p>
                    <h3 class="mt-4 text-2xl font-semibold text-zinc-950 dark:text-white">{{ auth()->user()->name }}</h3>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ auth()->user()->profile_headline ?: __('Build a strong public profile for your network.') }}</p>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-2xl border border-zinc-200/80 p-4 dark:border-zinc-700">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">{{ __('Referral Code') }}</p>
                            <p class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ auth()->user()->referral_code }}</p>
                        </div>
                        <div class="rounded-2xl border border-zinc-200/80 p-4 dark:border-zinc-700">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">{{ __('Referral Link') }}</p>
                            <p class="mt-2 break-all text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ auth()->user()->referral_link }}</p>
                        </div>
                        <div class="rounded-2xl border border-zinc-200/80 p-4 dark:border-zinc-700">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">{{ __('Phone') }}</p>
                            <p class="mt-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ auth()->user()->full_phone ?: __('Not set yet') }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[1.75rem] border border-zinc-200/80 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/70">
                        <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Core details') }}</h3>
                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />
                            <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />
                            <div class="grid gap-2">
                                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Country') }}</label>
                                <select wire:model.live="country_code" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-xs outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:ring-teal-900">
                                    @foreach ($countries as $country)
                                        <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <flux:input wire:model="city" :label="__('City')" type="text" />
                            <flux:input wire:model="phone_code" :label="__('Phone code')" type="text" readonly />
                            <flux:input wire:model="phone_number" :label="__('Phone number')" type="text" />
                        </div>

                        @if ($this->hasUnverifiedEmail)
                            <div>
                                <flux:text class="mt-4">
                                    {{ __('Your email address is unverified.') }}

                                    <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                        {{ __('Click here to re-send the verification email.') }}
                                    </flux:link>
                                </flux:text>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-[1.75rem] border border-zinc-200/80 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/70">
                        <h3 class="text-lg font-semibold text-zinc-950 dark:text-white">{{ __('Professional profile') }}</h3>
                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <flux:input wire:model="profession" :label="__('Profession')" type="text" />
                            <flux:input wire:model="company_name" :label="__('Company / Team Name')" type="text" />
                            <div class="md:col-span-2">
                                <flux:input wire:model="profile_headline" :label="__('Profile headline')" type="text" />
                            </div>
                            <div class="grid gap-2 md:col-span-2">
                                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Bio') }}</label>
                                <textarea wire:model="bio" rows="5" class="w-full rounded-[1.4rem] border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 shadow-xs outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:ring-teal-900"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-profile-button">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
