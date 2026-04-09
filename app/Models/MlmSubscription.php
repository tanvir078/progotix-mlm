<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'plan_id',
    'sponsor_id',
    'amount',
    'status',
    'started_at',
    'expires_at',
    'refund_requested_at',
    'refunded_at',
    'refunded_by',
    'refund_note',
])]
class MlmSubscription extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_UPGRADED = 'upgraded';

    public const STATUS_REFUNDED = 'refunded';

    public function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'refund_requested_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MlmPlan::class, 'plan_id');
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(MlmRefundRequest::class, 'subscription_id');
    }
}
