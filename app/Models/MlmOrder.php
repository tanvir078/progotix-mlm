<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'order_no',
    'status',
    'currency',
    'subtotal',
    'commission_amount',
    'team_bonus_amount',
    'commission_cycle',
    'total_bv',
    'notes',
    'refund_requested_at',
    'placed_at',
    'paid_at',
]
class MlmOrder extends Model
{
    public const STATUS_PAID = 'paid';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CANCELLED = 'cancelled';

    public function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'team_bonus_amount' => 'decimal:2',
            'commission_cycle' => 'integer',
            'total_bv' => 'decimal:2',
            'placed_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MlmOrderItem::class, 'order_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(MlmInvoice::class, 'order_id');
    }
}
