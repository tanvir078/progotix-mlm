<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'plan_id',
    'sponsor_id',
    'amount',
    'status',
    'started_at',
    'expires_at',
])]
class MlmSubscription extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_UPGRADED = 'upgraded';

    public function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
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
}
