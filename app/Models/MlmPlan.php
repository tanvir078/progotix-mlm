<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'code',
    'description',
    'price',
    'direct_bonus',
    'level_bonus',
    'is_active',
    'sort_order',
])]
class MlmPlan extends Model
{
    public function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'direct_bonus' => 'decimal:2',
            'level_bonus' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MlmSubscription::class, 'plan_id');
    }
}
