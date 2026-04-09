<x-layouts::app :title="__('Referral Network')">
    @php
        $networkCards = [
            [
                'label' => 'Direct Members',
                'value' => number_format((int) $downlineStats['direct_count']),
                'meta' => 'Level 1 sponsor line',
                'icon' => 'user-group',
                'tone' => 'brand',
            ],
            [
                'label' => 'Total Downline',
                'value' => number_format((int) $downlineStats['total_count']),
                'meta' => 'All tracked referral levels',
                'icon' => 'users',
                'tone' => 'accent',
            ],
            [
                'label' => 'Active Members',
                'value' => number_format((int) $downlineStats['active_count']),
                'meta' => 'Package active in team',
                'icon' => 'check-badge',
            ],
            [
                'label' => 'Depth Reached',
                'value' => number_format((int) $downlineStats['max_depth']),
                'meta' => 'Referral levels built',
                'icon' => 'squares-plus',
            ],
        ];

        $placementOptions = [
            'auto' => 'Auto placement',
            \App\Models\User::BINARY_LEFT => 'Left leg priority',
            \App\Models\User::BINARY_RIGHT => 'Right leg priority',
        ];
    @endphp

    <x-app-page spacing="loose">
        @if (session('status'))
            <div class="app-alert app-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="app-alert app-alert-danger">
                <p class="font-medium">Network action review করুন।</p>
                <p class="mt-1 text-sm">{{ $errors->first() }}</p>
            </div>
        @endif

        <x-app-panel variant="hero" padding="hero">
            <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr] xl:items-end">
                <div class="space-y-5">
                    <div class="space-y-4">
                        <p class="app-kicker">Referral & Downline Engine</p>
                        <div class="space-y-4">
                            <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-white sm:text-4xl xl:text-[2.7rem]">
                                Build the correct sponsor line, open downline IDs, and track every referral level from one mobile-first network desk.
                            </h1>
                            <p class="max-w-2xl text-sm leading-7 text-white/82 sm:text-base">
                                Fund movement, sponsor referral, binary placement, আর team growth যেন clear থাকে, সেজন্য network screen-এ share link, leg direction, direct team, আর deeper downline সব একসাথে সাজানো হয়েছে।
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('mlm.binary-tree') }}" class="app-pill app-pill-secondary" wire:navigate>Binary Tree</a>
                        <a href="{{ route('mlm.wallet') }}" class="app-pill app-pill-primary" wire:navigate>Fund Transfer</a>
                        <a href="{{ route('mlm.plans.index') }}" class="app-pill app-pill-primary" wire:navigate>Package Activation</a>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/60">Referral Link</p>
                        <p class="mt-3 break-all text-sm leading-6 text-white">{{ $user->referral_link }}</p>
                        <p class="mt-3 text-xs leading-5 text-white/70">Direct signup এই link থেকে হলে sponsor line automatically আপনার নামে যাবে।</p>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/58">Identity</p>
                        <div class="mt-3 space-y-2 text-sm text-white/82">
                            <p><span class="text-white/58">Username:</span> {{ '@'.$user->username }}</p>
                            <p><span class="text-white/58">Member ID:</span> {{ $user->member_code }}</p>
                            <p><span class="text-white/58">Current Rank:</span> {{ $user->currentRank?->name ?? 'Not ranked yet' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-app-panel>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($networkCards as $card)
                <x-app-stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :meta="$card['meta']"
                    :icon="$card['icon']"
                    :tone="$card['tone'] ?? 'default'"
                />
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <x-app-panel>
                <x-app-section-heading
                    title="Open Downline ID"
                    description="Sponsor হিসেবে নতুন member account create করুন, আর left/right/auto leg preference ঠিক করুন।"
                    eyebrow="Create Member"
                />

                <form method="POST" action="{{ route('mlm.network.downlines.store') }}" class="mt-6 space-y-4">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="app-form-label">Full Name</label>
                            <input name="name" type="text" value="{{ old('name') }}" class="app-form-control" placeholder="Member full name" />
                            @error('name')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="app-form-label">Username</label>
                            <input name="username" type="text" value="{{ old('username') }}" class="app-form-control" placeholder="unique username" />
                            @error('username')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="app-form-label">Email</label>
                            <input name="email" type="email" value="{{ old('email') }}" class="app-form-control" placeholder="member@example.com" />
                            @error('email')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="app-form-label">Country</label>
                            <select name="country_code" class="app-form-control">
                                @foreach (config('countries.list') as $country)
                                    <option value="{{ $country['code'] }}" @selected(old('country_code', $user->country_code ?? 'BD') === $country['code'])>
                                        {{ $country['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country_code')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="app-form-label">Phone Number</label>
                            <input name="phone_number" type="text" value="{{ old('phone_number') }}" class="app-form-control" placeholder="01700000000" />
                            @error('phone_number')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="app-form-label">City</label>
                            <input name="city" type="text" value="{{ old('city') }}" class="app-form-control" placeholder="City or region" />
                            @error('city')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="app-form-label">Placement Preference</label>
                            <select name="placement_preference" class="app-form-control">
                                @foreach ($placementOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('placement_preference', 'auto') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('placement_preference')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="app-metric-tile">
                            <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">Leg Status</p>
                            <p class="mt-2 text-sm font-semibold text-zinc-950 dark:text-white">
                                Left: {{ $placementAvailability['left_open'] ? 'Open' : 'Filled' }} •
                                Right: {{ $placementAvailability['right_open'] ? 'Open' : 'Filled' }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                Preferred leg full হলে system একই leg-এর নিচে next available slot খুঁজবে।
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="app-form-label">Password</label>
                            <input name="password" type="password" class="app-form-control" placeholder="Minimum 8 characters" />
                            @error('password')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="app-form-label">Confirm Password</label>
                            <input name="password_confirmation" type="password" class="app-form-control" placeholder="Repeat password" />
                        </div>
                    </div>

                    <button type="submit" class="app-button-primary w-full">
                        Open new downline ID
                    </button>
                </form>
            </x-app-panel>

            <div class="grid gap-6">
                <x-app-panel variant="contrast">
                    <x-app-section-heading
                        title="Referral Structure"
                        description="Sponsor line, binary position, আর direct team quality এক স্ক্রিনে।"
                        eyebrow="Placement"
                    />

                    <div class="mt-5 app-list-stack">
                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Sponsor / Referrer</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    {{ $user->referrer?->name ?? 'Direct signup / root member' }}
                                    @if ($user->referrer)
                                        • {{ '@'.$user->referrer->username }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Binary Parent</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    {{ $user->binaryParent?->name ?? 'Not placed yet' }}
                                    @if ($user->binaryParent)
                                        • {{ '@'.$user->binaryParent->username }} • {{ ucfirst($user->binary_position ?? 'auto') }} leg
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Direct team health</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    Active direct: {{ number_format((int) $downlineStats['active_direct_count']) }} •
                                    Pending direct: {{ number_format((int) $downlineStats['pending_direct_count']) }}
                                </p>
                            </div>
                        </div>

                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Current package</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    {{ $user->latestActiveSubscription?->plan?->name ?? 'No active package yet' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </x-app-panel>

                <x-app-panel>
                    <x-app-section-heading
                        title="Direct Members"
                        description="Level 1 members with sponsor identity, package state, and personal sub-team count."
                        eyebrow="Level 1"
                    />

                    <div class="mt-5 app-list-stack">
                        @forelse ($directReferrals as $member)
                            <div class="app-list-row">
                                <div class="space-y-2">
                                    <p class="font-semibold text-zinc-950 dark:text-white">{{ $member->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ '@'.$member->username }} • {{ $member->member_code }}</p>
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">
                                        {{ $member->latestActiveSubscription?->plan?->name ?? 'Pending activation' }}
                                    </p>
                                </div>

                                <div class="grid gap-4 text-sm text-zinc-600 dark:text-zinc-300 sm:grid-cols-2">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.24em] text-zinc-400">Joined</p>
                                        <p class="mt-1 font-semibold">{{ $member->created_at->format('d M Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.24em] text-zinc-400">Sub-Team</p>
                                        <p class="mt-1 font-semibold">{{ number_format((int) $member->referrals_count) }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="app-empty-state">
                                এখনো কোনো direct member নেই। Referral link share করুন অথবা এখান থেকেই নতুন downline ID খুলুন।
                            </div>
                        @endforelse
                    </div>
                </x-app-panel>
            </div>
        </section>

        <section class="grid gap-6">
            <x-app-panel>
                <x-app-section-heading
                    title="Downline Levels"
                    description="Referral sponsor tree অনুযায়ী level-by-level visibility, যাতে growth structure clear থাকে।"
                    eyebrow="Team Depth"
                />

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    @forelse ($downlineLevels as $level => $members)
                        <div class="app-panel app-panel-soft p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="app-kicker">Level {{ $level }}</p>
                                    <h3 class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ number_format((int) $members->count()) }} members</h3>
                                </div>
                                <span class="app-status-badge {{ $level === 1 ? 'app-status-badge-success' : 'app-status-badge-warning' }}">
                                    {{ $level === 1 ? 'Direct' : 'Downline' }}
                                </span>
                            </div>

                            <div class="mt-5 space-y-3">
                                @foreach ($members as $member)
                                    <div class="rounded-[1.3rem] border border-zinc-200/80 bg-white/80 p-4 dark:border-zinc-700 dark:bg-zinc-900/60">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="space-y-1">
                                                <p class="font-semibold text-zinc-950 dark:text-white">{{ $member->name }}</p>
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ '@'.$member->username }} • {{ $member->member_code }}</p>
                                                <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">
                                                    {{ $member->latestActiveSubscription?->plan?->name ?? 'Not activated' }}
                                                </p>
                                            </div>

                                            <div class="grid gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                                                <p>Joined: {{ $member->created_at->format('d M Y') }}</p>
                                                <p>Direct: {{ number_format((int) $member->referrals_count) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="app-empty-state lg:col-span-2">
                            Referral sponsor line-এ এখনো কোনো downline তৈরি হয়নি।
                        </div>
                    @endforelse
                </div>
            </x-app-panel>
        </section>
    </x-app-page>
</x-layouts::app>
