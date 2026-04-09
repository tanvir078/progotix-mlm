<x-layouts::app :title="__('Payment Methods')">
    <x-app-page spacing="relaxed">
        @if (session('status'))
            <div class="app-alert app-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="app-alert app-alert-danger">
                <p class="font-medium">Payment method form review করুন।</p>
                <p class="mt-1 text-sm">{{ $errors->first() }}</p>
            </div>
        @endif

        <x-app-panel variant="hero" padding="hero">
            <div class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr] xl:items-end">
                <div class="space-y-4">
                    <p class="app-kicker">Payment Method Catalog</p>
                    <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                        Configure country-wise e-wallet, bank, card, and crypto channels from one admin surface.
                    </h1>
                    <p class="max-w-2xl text-sm leading-7 text-white/82 sm:text-base">
                        Shared catalog থেকে deposit আর withdrawal দুই flow চালানো হবে, তাই support flags আর charge settings clean রাখা জরুরি।
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-2">
                    <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/62">Active</p>
                        <p class="mt-3 text-3xl font-semibold text-white">{{ number_format((int) $stats['active_count']) }}</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/58">Country Scoped</p>
                        <p class="mt-3 text-3xl font-semibold text-white">{{ number_format((int) $stats['country_scoped']) }}</p>
                    </div>
                </div>
            </div>
        </x-app-panel>

        <x-app-panel>
            <x-app-section-heading
                title="Create Payment Method"
                description="New channel add করুন, তারপর member surface আর admin queue-তে automatically show হবে."
                eyebrow="New Method"
            />

            <form method="POST" action="{{ route('admin.payment-methods.store') }}" class="mt-6 grid gap-4 lg:grid-cols-2">
                @csrf

                <div>
                    <label class="app-form-label">Name</label>
                    <input name="name" value="{{ old('name') }}" class="app-form-control" placeholder="bKash Personal" />
                </div>

                <div>
                    <label class="app-form-label">Code</label>
                    <input name="code" value="{{ old('code') }}" class="app-form-control" placeholder="BKASH_BD" />
                </div>

                <div>
                    <label class="app-form-label">Type</label>
                    <select name="type" class="app-form-control">
                        @foreach ($types as $type)
                            <option value="{{ $type }}" @selected(old('type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="app-form-label">Country Code</label>
                    <input name="country_code" value="{{ old('country_code') }}" class="app-form-control" placeholder="BD or leave blank for Global" />
                </div>

                <div>
                    <label class="app-form-label">Currency Code</label>
                    <input name="currency_code" value="{{ old('currency_code', 'USD') }}" class="app-form-control" placeholder="USD" />
                </div>

                <div>
                    <label class="app-form-label">Provider Name</label>
                    <input name="provider_name" value="{{ old('provider_name') }}" class="app-form-control" placeholder="Stripe, Binance, Local Bank" />
                </div>

                <div>
                    <label class="app-form-label">Destination Label</label>
                    <input name="destination_label" value="{{ old('destination_label') }}" class="app-form-control" placeholder="Wallet Number, IBAN, TRC20 Address" />
                </div>

                <div>
                    <label class="app-form-label">Destination Value</label>
                    <input name="destination_value" value="{{ old('destination_value') }}" class="app-form-control" placeholder="017..., AE..., TX..." />
                </div>

                <div>
                    <label class="app-form-label">Minimum Amount</label>
                    <input name="min_amount" type="number" min="0" step="0.01" value="{{ old('min_amount', '0') }}" class="app-form-control" />
                </div>

                <div>
                    <label class="app-form-label">Maximum Amount</label>
                    <input name="max_amount" type="number" min="0" step="0.01" value="{{ old('max_amount') }}" class="app-form-control" />
                </div>

                <div>
                    <label class="app-form-label">Fixed Charge</label>
                    <input name="fixed_charge" type="number" min="0" step="0.01" value="{{ old('fixed_charge', '0') }}" class="app-form-control" />
                </div>

                <div>
                    <label class="app-form-label">Percent Charge Rate</label>
                    <input name="percent_charge_rate" type="number" min="0" max="1" step="0.0001" value="{{ old('percent_charge_rate', '0') }}" class="app-form-control" placeholder="0.025 = 2.5%" />
                </div>

                <div class="lg:col-span-2">
                    <label class="app-form-label">Instructions</label>
                    <textarea name="instructions" rows="4" class="app-form-control app-form-textarea" placeholder="Manual verification instructions for members">{{ old('instructions') }}</textarea>
                </div>

                <div class="grid gap-3 sm:grid-cols-4 lg:col-span-2">
                    <label class="app-list-row cursor-pointer"><input type="checkbox" name="supports_deposit" value="1" checked class="mt-1" /><span>Deposit</span></label>
                    <label class="app-list-row cursor-pointer"><input type="checkbox" name="supports_withdrawal" value="1" class="mt-1" /><span>Withdrawal</span></label>
                    <label class="app-list-row cursor-pointer"><input type="checkbox" name="is_active" value="1" checked class="mt-1" /><span>Active</span></label>
                    <div>
                        <label class="app-form-label">Sort Order</label>
                        <input name="sort_order" type="number" min="0" step="1" value="{{ old('sort_order', '0') }}" class="app-form-control" />
                    </div>
                </div>

                <button type="submit" class="app-button-primary lg:col-span-2">Create payment method</button>
            </form>
        </x-app-panel>

        <section class="app-list-stack">
            @foreach ($methods as $method)
                <x-app-panel>
                    <form method="POST" action="{{ route('admin.payment-methods.update', $method) }}" class="grid gap-4 lg:grid-cols-2">
                        @csrf
                        @method('PATCH')

                        <div class="lg:col-span-2 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-lg font-semibold text-zinc-950 dark:text-white">{{ $method->name }}</p>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $method->type_label }} • {{ $method->country_label }} • {{ $method->currency_code }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if ($method->supports_deposit)
                                    <span class="app-status-badge app-status-badge-success">Deposit</span>
                                @endif
                                @if ($method->supports_withdrawal)
                                    <span class="app-status-badge app-status-badge-warning">Withdrawal</span>
                                @endif
                                <span class="app-status-badge {{ $method->is_active ? 'app-status-badge-success' : 'app-status-badge-neutral' }}">
                                    {{ $method->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="app-form-label">Name</label>
                            <input name="name" value="{{ $method->name }}" class="app-form-control" />
                        </div>
                        <div>
                            <label class="app-form-label">Code</label>
                            <input name="code" value="{{ $method->code }}" class="app-form-control" />
                        </div>
                        <div>
                            <label class="app-form-label">Type</label>
                            <select name="type" class="app-form-control">
                                @foreach ($types as $type)
                                    <option value="{{ $type }}" @selected($method->type === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="app-form-label">Country Code</label>
                            <input name="country_code" value="{{ $method->country_code }}" class="app-form-control" />
                        </div>
                        <div>
                            <label class="app-form-label">Currency Code</label>
                            <input name="currency_code" value="{{ $method->currency_code }}" class="app-form-control" />
                        </div>
                        <div>
                            <label class="app-form-label">Provider Name</label>
                            <input name="provider_name" value="{{ $method->provider_name }}" class="app-form-control" />
                        </div>
                        <div>
                            <label class="app-form-label">Destination Label</label>
                            <input name="destination_label" value="{{ $method->destination_label }}" class="app-form-control" />
                        </div>
                        <div>
                            <label class="app-form-label">Destination Value</label>
                            <input name="destination_value" value="{{ $method->destination_value }}" class="app-form-control" />
                        </div>
                        <div>
                            <label class="app-form-label">Minimum Amount</label>
                            <input name="min_amount" type="number" min="0" step="0.01" value="{{ $method->min_amount }}" class="app-form-control" />
                        </div>
                        <div>
                            <label class="app-form-label">Maximum Amount</label>
                            <input name="max_amount" type="number" min="0" step="0.01" value="{{ $method->max_amount }}" class="app-form-control" />
                        </div>
                        <div>
                            <label class="app-form-label">Fixed Charge</label>
                            <input name="fixed_charge" type="number" min="0" step="0.01" value="{{ $method->fixed_charge }}" class="app-form-control" />
                        </div>
                        <div>
                            <label class="app-form-label">Percent Charge Rate</label>
                            <input name="percent_charge_rate" type="number" min="0" max="1" step="0.0001" value="{{ $method->percent_charge_rate }}" class="app-form-control" />
                        </div>
                        <div class="lg:col-span-2">
                            <label class="app-form-label">Instructions</label>
                            <textarea name="instructions" rows="3" class="app-form-control app-form-textarea">{{ $method->instructions }}</textarea>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-4 lg:col-span-2">
                            <label class="app-list-row cursor-pointer"><input type="checkbox" name="supports_deposit" value="1" @checked($method->supports_deposit) class="mt-1" /><span>Deposit</span></label>
                            <label class="app-list-row cursor-pointer"><input type="checkbox" name="supports_withdrawal" value="1" @checked($method->supports_withdrawal) class="mt-1" /><span>Withdrawal</span></label>
                            <label class="app-list-row cursor-pointer"><input type="checkbox" name="is_active" value="1" @checked($method->is_active) class="mt-1" /><span>Active</span></label>
                            <div>
                                <label class="app-form-label">Sort Order</label>
                                <input name="sort_order" type="number" min="0" step="1" value="{{ $method->sort_order }}" class="app-form-control" />
                            </div>
                        </div>
                        <div class="lg:col-span-2">
                            <button type="submit" class="app-button-primary">Update method</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-[1.15rem] bg-rose-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-rose-500">
                            Delete
                        </button>
                    </form>
                </x-app-panel>
            @endforeach
        </section>

        <div>{{ $methods->links() }}</div>
    </x-app-page>
</x-layouts::app>
