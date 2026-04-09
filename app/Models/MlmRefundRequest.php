<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'subscription_id',
    'order_id',
    'invoice_id',
    'requested_by',
    'processed_by',
    'type',
    'status',
    'amount',
    'commission_reversal_amount',
    'reason',
    'admin_note',
    'requested_at',
    'processed_at',
])]
class MlmRefundRequest extends Model
{
    public const TYPE_SUBSCRIPTION = 'subscription';

    public const TYPE_ORDER = 'order';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'commission_reversal_amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(MlmSubscription::class, 'subscription_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(MlmOrder::class, 'order_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(MlmInvoice::class, 'invoice_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(MlmTransaction::class, 'refund_request_id');
    }
}
