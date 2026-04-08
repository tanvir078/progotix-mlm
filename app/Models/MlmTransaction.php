<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'source_user_id',
    'subscription_id',
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

    public const TYPE_WITHDRAWAL = 'withdrawal';

    public function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'posted_at' => 'datetime',
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
}
