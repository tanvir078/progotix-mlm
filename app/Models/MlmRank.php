<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'badge_color',
    'direct_referrals_required',
    'personal_sales_required',
    'team_volume_required',
    'bonus_amount',
    'sort_order',
])]
class MlmRank extends Model
{
    public function casts(): array
    {
        return [
            'personal_sales_required' => 'decimal:2',
            'team_volume_required' => 'decimal:2',
            'bonus_amount' => 'decimal:2',
        ];
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(MlmRankAchievement::class, 'rank_id');
    }
}
