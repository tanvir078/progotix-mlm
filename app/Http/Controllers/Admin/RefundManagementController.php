<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProcessRefundRequest;
use App\Http\Requests\Admin\StoreRefundRequest;
use App\Models\MlmOrder;
use App\Models\MlmRefundRequest;
use App\Models\MlmSubscription;
use App\Models\MlmTransaction;
use App\Services\RefundWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RefundManagementController extends Controller
{
    public function __construct(
        private readonly RefundWorkflowService $refundWorkflowService,
    ) {}

    public function index(Request $request): View
    {
        $status = trim((string) $request->string('status', MlmRefundRequest::STATUS_PENDING));
        $type = trim((string) $request->string('type', 'all'));

        $requests = MlmRefundRequest::query()
            ->with(['user', 'subscription.plan', 'order.items', 'invoice', 'requester', 'processor'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($type !== 'all', fn ($query) => $query->where('type', $type))
            ->latest('requested_at')
            ->paginate(12)
            ->withQueryString();

        $pendingSubscriptionIds = MlmRefundRequest::query()
            ->where('status', MlmRefundRequest::STATUS_PENDING)
            ->whereNotNull('subscription_id')
            ->pluck('subscription_id');

        $pendingOrderIds = MlmRefundRequest::query()
            ->where('status', MlmRefundRequest::STATUS_PENDING)
            ->whereNotNull('order_id')
            ->pluck('order_id');

        return view('admin.refunds', [
            'requests' => $requests,
            'status' => $status,
            'type' => $type,
            'eligibleSubscriptions' => MlmSubscription::query()
                ->with(['user', 'plan'])
                ->whereIn('status', [
                    MlmSubscription::STATUS_ACTIVE,
                    MlmSubscription::STATUS_UPGRADED,
                ])
                ->whereNotIn('id', $pendingSubscriptionIds)
                ->latest('started_at')
                ->take(8)
                ->get(),
            'eligibleOrders' => MlmOrder::query()
                ->with(['user', 'items'])
                ->where('status', MlmOrder::STATUS_PAID)
                ->whereNotIn('id', $pendingOrderIds)
                ->latest('paid_at')
                ->take(8)
                ->get(),
            'reversalTransactions' => MlmTransaction::query()
                ->with(['user', 'sourceUser', 'refundRequest'])
                ->whereNotNull('refund_request_id')
                ->whereIn('type', MlmTransaction::commissionReversalTypes())
                ->latest('posted_at')
                ->take(12)
                ->get(),
            'stats' => [
                'pending_count' => MlmRefundRequest::query()->where('status', MlmRefundRequest::STATUS_PENDING)->count(),
                'pending_amount' => (float) MlmRefundRequest::query()->where('status', MlmRefundRequest::STATUS_PENDING)->sum('amount'),
                'approved_amount' => (float) MlmRefundRequest::query()->where('status', MlmRefundRequest::STATUS_APPROVED)->sum('amount'),
                'reversed_commissions' => (float) MlmTransaction::query()
                    ->whereIn('type', MlmTransaction::commissionReversalTypes())
                    ->sum('amount'),
            ],
        ]);
    }

    public function store(StoreRefundRequest $request): RedirectResponse
    {
        if ($request->type() === MlmRefundRequest::TYPE_SUBSCRIPTION) {
            $subscription = MlmSubscription::query()->findOrFail($request->subscriptionId());

            $this->refundWorkflowService->submitSubscriptionRefund(
                $request->user(),
                $subscription,
                $request->reason(),
                $request->adminNote(),
            );
        }

        if ($request->type() === MlmRefundRequest::TYPE_ORDER) {
            $order = MlmOrder::query()->findOrFail($request->orderId());

            $this->refundWorkflowService->submitOrderRefund(
                $request->user(),
                $order,
                $request->reason(),
                $request->adminNote(),
            );
        }

        return back()->with('status', 'Refund request queued successfully.');
    }

    public function update(ProcessRefundRequest $request, MlmRefundRequest $refundRequest): RedirectResponse
    {
        $this->refundWorkflowService->process(
            $request->user(),
            $refundRequest,
            $request->decision(),
            $request->adminNote(),
        );

        return back()->with('status', 'Refund request processed successfully.');
    }
}
