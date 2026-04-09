<?php

namespace App\Services;

use App\Models\MlmInvoice;
use App\Models\MlmOrder;
use App\Models\MlmProduct;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly ReferenceNumberService $referenceNumberService,
    ) {}

    /**
     * @return array{order:MlmOrder,invoice:MlmInvoice}
     */
    public function place(User $user, MlmProduct $product, int $quantity = 1): array
    {
        return DB::transaction(function () use ($user, $product, $quantity): array {
            $subtotal = round((float) $product->price * $quantity, 2);
            $totalBv = round((float) $product->bv * $quantity, 2);
            $commissionAmount = round($subtotal * (float) $product->retail_commission_rate, 2);
            $teamBonusAmount = round($subtotal * (float) $product->team_bonus_rate, 2);

            $order = $user->orders()->create([
                'order_no' => $this->referenceNumberService->nextOrderNo(),
                'status' => MlmOrder::STATUS_PENDING,
                'currency' => 'USD',
                'subtotal' => $subtotal,
                'commission_amount' => $commissionAmount,
                'team_bonus_amount' => $teamBonusAmount,
                'total_bv' => $totalBv,
                'notes' => $product->name.' retail order',
                'placed_at' => now(),
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'line_total' => $subtotal,
                'line_bv' => $totalBv,
            ]);

            $invoice = $user->invoices()->create([
                'order_id' => $order->id,
                'invoice_no' => $this->referenceNumberService->nextInvoiceNo(),
                'title' => 'Retail order '.$order->order_no,
                'amount' => $subtotal,
                'status' => MlmInvoice::STATUS_PENDING,
                'issued_at' => now(),
                'due_at' => now(),
                'notes' => 'Awaiting payment confirmation before commission posting.',
            ]);

            return [
                'order' => $order,
                'invoice' => $invoice,
            ];
        });
    }
}
