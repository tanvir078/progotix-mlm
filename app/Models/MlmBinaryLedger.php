<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'left_volume',
    'right_volume',
    'left_carry',
    'right_carry',
    'pair_volume',
    'bonus_rate',
    'total_binary_bonus',
    'last_paired_at',
])]
class MlmBinaryLedger extends Model
{
    public function casts(): array
    {
        return [
            'left_volume' => 'decimal:2',
            'right_volume' => 'decimal:2',
            'left_carry' => 'decimal:2',
            'right_carry' => 'decimal:2',
            'pair_volume' => 'decimal:2',
            'bonus_rate' => 'decimal:4',
            'total_binary_bonus' => 'decimal:2',
            'last_paired_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
