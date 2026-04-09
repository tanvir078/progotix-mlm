<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'code',
    'type',
    'country_code',
    'currency_code',
    'provider_name',
    'destination_label',
    'destination_value',
    'instructions',
    'min_amount',
    'max_amount',
    'fixed_charge',
    'percent_charge_rate',
    'supports_deposit',
    'supports_withdrawal',
    'is_active',
    'sort_order',
])]
class MlmPaymentMethod extends Model
{
    public const TYPE_E_WALLET = 'e_wallet';

    public const TYPE_BANK = 'bank';

    public const TYPE_CARD = 'card';

    public const TYPE_CRYPTO = 'crypto';

    public function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'fixed_charge' => 'decimal:2',
            'percent_charge_rate' => 'decimal:4',
            'supports_deposit' => 'boolean',
            'supports_withdrawal' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(MlmDepositRequest::class, 'payment_method_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(MlmWithdrawalRequest::class, 'payment_method_id');
    }

    public function typeLabel(): Attribute
    {
        return Attribute::get(function (): string {
            return match ($this->type) {
                self::TYPE_E_WALLET => 'E-Wallet',
                self::TYPE_BANK => 'Bank',
                self::TYPE_CARD => 'Card',
                self::TYPE_CRYPTO => 'Crypto',
                default => ucfirst(str_replace('_', ' ', $this->type)),
            };
        });
    }

    public function countryLabel(): Attribute
    {
        return Attribute::get(function (): string {
            if (! $this->country_code) {
                return 'Global';
            }

            return collect(config('countries.list'))
                ->firstWhere('code', $this->country_code)['name'] ?? $this->country_code;
        });
    }

    /**
     * @return array{charge_amount:float,net_amount:float}
     */
    public function chargePreview(float $amount): array
    {
        $chargeAmount = round(((float) $this->percent_charge_rate * $amount) + (float) $this->fixed_charge, 2);
        $netAmount = max(0, round($amount - $chargeAmount, 2));

        return [
            'charge_amount' => $chargeAmount,
            'net_amount' => $netAmount,
        ];
    }
}
