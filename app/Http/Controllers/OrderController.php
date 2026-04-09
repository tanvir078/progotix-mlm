<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mlm\PlaceOrderRequest;
use App\Models\MlmOrder;
use App\Models\MlmProduct;
use App\Models\MlmTransaction;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $orderCount = $user->orders()->count();
        $retailSales = (float) $user->retailSalesTotal();

        return view('mlm.orders', [
            'orders' => $user->orders()
                ->with('items')
                ->latest('placed_at')
                ->paginate(10),
            'totals' => [
                'order_count' => $orderCount,
                'retail_sales' => $retailSales,
                'commission' => (float) $user->transactions()->where('type', MlmTransaction::TYPE_RETAIL_COMMISSION)->sum('amount'),
                'team_sales_bonus' => (float) $user->transactions()->where('type', MlmTransaction::TYPE_TEAM_SALES_BONUS)->sum('amount'),
                'total_bv' => (float) $user->orders()->where('status', MlmOrder::STATUS_PAID)->sum('total_bv'),
                'average_order_value' => $orderCount > 0 ? round($retailSales / $orderCount, 2) : 0,
            ],
        ]);
    }

    public function store(PlaceOrderRequest $request, MlmProduct $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $result = $this->orderService->place($request->user(), $product, $request->quantity());

        return redirect()
            ->route('mlm.orders')
            ->with('status', 'Order '.$result['order']->order_no.' submitted and awaiting payment confirmation.');
    }
}
