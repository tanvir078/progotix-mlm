<?php

namespace App\Livewire\Admin;

use App\Models\MlmOrder;
use App\Models\MlmSubscription;
use App\Services\CommissionService;
use Livewire\Component;
use Livewire\WithPagination;

class RefundRequests extends Component
{
    use WithPagination;

    public string $filterType = 'all';
    public $selected = [];

    public function mount()
    {
        session()->flash('info', 'Pending refunds trigger automatic commission reversals when approved.');
    }

    public function approve($id, $type)
    {
        if ($type === 'subscription') {
            $subscription = MlmSubscription::findOrFail($id);
            app(CommissionService::class)->reverseSubscriptionBonuses($subscription->user, $subscription);
            $subscription->update(['status' => 'refunded']);
        } elseif ($type === 'order') {
            $order = MlmOrder::findOrFail($id);
            app(CommissionService::class)->reverseRetailOrderCommissions($order, $order->user);
            $order->update(['status' => 'refunded']);
        }

        session()->flash('message', 'Refund approved and commissions reversed.');
        $this->reset(['selected']);
    }

    public function bulkApprove()
    {
        $approved = 0;
        foreach ($this->selected as $item) {
            [$type, $id] = explode(':', $item);
            $this->approve($id, $type);
            $approved++;
        }
        session()->flash('message', "{$approved} refunds processed.");
    }

    public function render()
    {
        $refunds = match($this->filterType) {
            'subscription' => MlmSubscription::where('status', '!=', 'active')
                ->with('user', 'plan')
                ->latest()
                ->paginate(15),
            'order' => MlmOrder::whereIn('status', ['pending', 'paid', 'refund_requested'])
                ->with('user', 'items')
                ->latest()
                ->paginate(15),
            default => collect()
                ->merge(MlmSubscription::where('status', 'refund_requested')->with('user')->get())
                ->merge(MlmOrder::where('status', 'refund_requested')->with('user')->get())
                ->sortByDesc('updated_at')
                ->paginate(15)
        };

        return view('livewire.admin.refund-requests', [
            'refunds' => $refunds
        ]);
    }
}

