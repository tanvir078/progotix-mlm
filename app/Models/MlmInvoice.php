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
    'invoice_no',
    'title',
    'amount',
    'status',
    'issued_at',
    'due_at',
    'paid_at',
    'refunded_at',
    'notes',
    'refund_note',
])]
class MlmInvoice extends Model
{
    public const STATUS_PAID = 'paid';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_REFUNDED = 'refunded';

    public function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
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

    public function refundRequests(): HasMany
    {
        return $this->hasMany(MlmRefundRequest::class, 'invoice_id');
    }
}
