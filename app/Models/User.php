<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable([
    'name',
    'username',
    'member_code',
    'email',
    'country_code',
    'phone_code',
    'phone_number',
    'city',
    'profession',
    'company_name',
    'profile_headline',
    'bio',
    'password',
    'referrer_id',
    'binary_parent_id',
    'binary_position',
    'balance',
    'is_admin',
    'current_rank_id',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    public const BINARY_LEFT = 'left';

    public const BINARY_RIGHT = 'right';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if ($user->username) {
                $user->username = Str::lower($user->username);
            }

            if (! $user->member_code) {
                $user->member_code = static::generateMemberCode();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Sponsor/referrer relationship for the MLM tree.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Direct referrals sponsored by this user.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    public function binaryParent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'binary_parent_id');
    }

    public function binaryChildren(): HasMany
    {
        return $this->hasMany(User::class, 'binary_parent_id');
    }

    public function leftBinaryChild(): HasMany
    {
        return $this->binaryChildren()->where('binary_position', self::BINARY_LEFT);
    }

    public function rightBinaryChild(): HasMany
    {
        return $this->binaryChildren()->where('binary_position', self::BINARY_RIGHT);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MlmSubscription::class);
    }

    public function latestActiveSubscription(): HasOne
    {
        return $this->hasOne(MlmSubscription::class)
            ->where('status', MlmSubscription::STATUS_ACTIVE)
            ->latestOfMany('started_at');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MlmTransaction::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(MlmInvoice::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(MlmOrder::class);
    }

    public function binaryLedger(): HasOne
    {
        return $this->hasOne(MlmBinaryLedger::class);
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(MlmWithdrawalRequest::class);
    }

    public function depositRequests(): HasMany
    {
        return $this->hasMany(MlmDepositRequest::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(MlmRefundRequest::class);
    }

    public function walletTransfersSent(): HasMany
    {
        return $this->hasMany(MlmWalletTransfer::class, 'sender_id');
    }

    public function walletTransfersReceived(): HasMany
    {
        return $this->hasMany(MlmWalletTransfer::class, 'receiver_id');
    }

    public function currentRank(): BelongsTo
    {
        return $this->belongsTo(MlmRank::class, 'current_rank_id');
    }

    public function rankAchievements(): HasMany
    {
        return $this->hasMany(MlmRankAchievement::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MlmDocument::class);
    }

    public function referralLink(): Attribute
    {
        return Attribute::get(fn (): string => route('register', ['ref' => $this->username]));
    }

    public function referralCode(): Attribute
    {
        return Attribute::get(fn (): string => (string) $this->username);
    }

    public function countryName(): Attribute
    {
        return Attribute::get(function (): ?string {
            return collect(config('countries.list'))
                ->firstWhere('code', $this->country_code)['name'] ?? null;
        });
    }

    public function fullPhone(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->phone_number) {
                return null;
            }

            return trim(($this->phone_code ? $this->phone_code.' ' : '').$this->phone_number);
        });
    }

    public function activeSubscription(): ?MlmSubscription
    {
        return $this->subscriptions()
            ->where('status', MlmSubscription::STATUS_ACTIVE)
            ->latest('started_at')
            ->with('plan')
            ->first();
    }

    public function teamCount(): int
    {
        $this->loadMissing('referrals');

        return $this->referrals->sum(fn (self $referral) => 1 + $referral->teamCount());
    }

    public function binaryTeamCount(): int
    {
        $this->loadMissing('binaryChildren');

        return $this->binaryChildren->sum(fn (self $member) => 1 + $member->binaryTeamCount());
    }

    public function pendingWithdrawalTotal(): string
    {
        return (string) $this->withdrawalRequests()
            ->where('status', MlmWithdrawalRequest::STATUS_PENDING)
            ->sum('amount');
    }

    public function binaryBonusTotal(): string
    {
        return (string) $this->transactions()
            ->where('type', MlmTransaction::TYPE_BINARY_BONUS)
            ->sum('amount');
    }

    public function retailSalesTotal(): string
    {
        return (string) $this->orders()
            ->where('status', MlmOrder::STATUS_PAID)
            ->sum('subtotal');
    }

    public function teamSalesVolume(): string
    {
        $ownVolume = (float) $this->orders()
            ->where('status', MlmOrder::STATUS_PAID)
            ->sum('total_bv');

        $directVolume = (float) MlmOrder::query()
            ->whereIn('user_id', $this->referrals()->pluck('id'))
            ->where('status', MlmOrder::STATUS_PAID)
            ->sum('total_bv');

        return (string) round($ownVolume + $directVolume, 2);
    }

    protected static function generateMemberCode(): string
    {
        do {
            $code = 'PGX-'.Str::upper(Str::random(7));
        } while (static::query()->where('member_code', $code)->exists());

        return $code;
    }
}
