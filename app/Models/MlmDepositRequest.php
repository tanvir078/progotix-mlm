<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'payment_method_id',
    'payment_method_name',
    'payment_method_type',
    'payment_method_snapshot',
    'currency',
    'amount',
    'charge_amount',
    'net_amount',
    'sender_name',
    'sender_account',
    'transaction_reference',
    'payment_proof_path',
    'note',
    'status',
    'admin_note',
    'processed_by',
    'submitted_at',
    'processed_at',
])]
class MlmDepositRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public function casts(): array
    {
        return [
            'payment_method_snapshot' => 'array',
            'amount' => 'decimal:2',
            'charge_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(MlmPaymentMethod::class, 'payment_method_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
