<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'sku',
    'name',
    'slug',
    'category',
    'description',
    'price',
    'bv',
    'retail_commission_rate',
    'team_bonus_rate',
    'is_active',
    'sort_order',
])]
class MlmProduct extends Model
{
    public function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'bv' => 'decimal:2',
            'retail_commission_rate' => 'decimal:4',
            'team_bonus_rate' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(MlmOrderItem::class, 'product_id');
    }
}
