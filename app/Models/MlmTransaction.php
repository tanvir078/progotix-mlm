<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'source_user_id',
    'subscription_id',
    'order_id',
    'deposit_request_id',
    'refund_request_id',
    'reference_key',
    'commission_level',
    'type',
    'direction',
    'amount',
    'title',
    'note',
    'posted_at',
])]
class MlmTransaction extends Model
{
    public const TYPE_DIRECT_BONUS = 'direct_bonus';

    public const TYPE_LEVEL_BONUS = 'level_bonus';

    public const TYPE_BINARY_BONUS = 'binary_bonus';

    public const TYPE_DIRECT_BONUS_REVERSAL = 'direct_bonus_reversal';

    public const TYPE_LEVEL_BONUS_REVERSAL = 'level_bonus_reversal';

    public const TYPE_BINARY_BONUS_REVERSAL = 'binary_bonus_reversal';

    public const TYPE_WITHDRAWAL = 'withdrawal';

    public const TYPE_DEPOSIT = 'deposit';

    public const TYPE_RETAIL_COMMISSION = 'retail_commission';

    public const TYPE_TEAM_SALES_BONUS = 'team_sales_bonus';

    public const TYPE_RETAIL_COMMISSION_REVERSAL = 'retail_commission_reversal';

    public const TYPE_TEAM_SALES_BONUS_REVERSAL = 'team_sales_bonus_reversal';

    public const TYPE_WALLET_TRANSFER_OUT = 'wallet_transfer_out';

    public const TYPE_WALLET_TRANSFER_IN = 'wallet_transfer_in';

    public const TYPE_WALLET_TRANSFER_FEE = 'wallet_transfer_fee';

    public const TYPE_RANK_BONUS = 'rank_bonus';

    public function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'commission_level' => 'integer',
            'posted_at' => 'datetime',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function commissionCreditTypes(): array
    {
        return [
            self::TYPE_DIRECT_BONUS,
            self::TYPE_LEVEL_BONUS,
            self::TYPE_BINARY_BONUS,
            self::TYPE_RETAIL_COMMISSION,
            self::TYPE_TEAM_SALES_BONUS,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function commissionReversalTypes(): array
    {
        return [
            self::TYPE_DIRECT_BONUS_REVERSAL,
            self::TYPE_LEVEL_BONUS_REVERSAL,
            self::TYPE_BINARY_BONUS_REVERSAL,
            self::TYPE_RETAIL_COMMISSION_REVERSAL,
            self::TYPE_TEAM_SALES_BONUS_REVERSAL,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'source_user_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(MlmSubscription::class, 'subscription_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(MlmOrder::class, 'order_id');
    }

    public function depositRequest(): BelongsTo
    {
        return $this->belongsTo(MlmDepositRequest::class, 'deposit_request_id');
    }

    public function refundRequest(): BelongsTo
    {
        return $this->belongsTo(MlmRefundRequest::class, 'refund_request_id');
    }
}
