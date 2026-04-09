<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\MlmOrder;
use App\Services\OrderWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderManagementController extends Controller
{
    public function __construct(
        private readonly OrderWorkflowService $orderWorkflowService,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $statuses = [
            MlmOrder::STATUS_PAID,
            MlmOrder::STATUS_PENDING,
            MlmOrder::STATUS_CANCELLED,
        ];

        $orders = MlmOrder::query()
            ->with(['user', 'items'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('order_no', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('member_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($status, $statuses, true), fn ($query) => $query->where('status', $status))
            ->latest('placed_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.orders', [
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
            'statuses' => $statuses,
            'stats' => [
                'paid_revenue' => (float) MlmOrder::query()->where('status', MlmOrder::STATUS_PAID)->sum('subtotal'),
                'pending_orders' => MlmOrder::query()->where('status', MlmOrder::STATUS_PENDING)->count(),
                'cancelled_orders' => MlmOrder::query()->where('status', MlmOrder::STATUS_CANCELLED)->count(),
                'commission_volume' => (float) MlmOrder::query()->where('status', MlmOrder::STATUS_PAID)->sum('commission_amount'),
            ],
        ]);
    }

    public function update(UpdateOrderStatusRequest $request, MlmOrder $order): RedirectResponse
    {
        $this->orderWorkflowService->transition(
            $order,
            $request->status(),
            $request->notes(),
        );

        return back()->with('status', 'Order updated successfully.');
    }
}
