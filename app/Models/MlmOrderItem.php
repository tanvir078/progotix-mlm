<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'product_id',
    'product_name',
    'sku',
    'quantity',
    'unit_price',
    'line_total',
    'line_bv',
])]
class MlmOrderItem extends Model
{
    public function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'line_bv' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(MlmOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MlmProduct::class, 'product_id');
    }
}
