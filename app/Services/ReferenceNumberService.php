<?php

namespace App\Services;

use App\Models\MlmInvoice;
use App\Models\MlmOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReferenceNumberService
{
    public function nextOrderNo(): string
    {
        return $this->uniqueNumber(MlmOrder::class, 'order_no', 'ORD');
    }

    public function nextInvoiceNo(): string
    {
        return $this->uniqueNumber(MlmInvoice::class, 'invoice_no', 'INV');
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function uniqueNumber(string $modelClass, string $column, string $prefix): string
    {
        do {
            $candidate = $prefix.'-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while ($modelClass::query()->where($column, $candidate)->exists());

        return $candidate;
    }
}
