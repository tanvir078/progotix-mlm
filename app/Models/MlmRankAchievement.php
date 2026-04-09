<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'rank_id',
    'bonus_amount',
    'achieved_at',
])]
class MlmRankAchievement extends Model
{
    public function casts(): array
    {
        return [
            'bonus_amount' => 'decimal:2',
            'achieved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(MlmRank::class, 'rank_id');
    }
}
