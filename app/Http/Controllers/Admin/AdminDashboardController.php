<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlmInvoice;
use App\Models\MlmSubscription;
use App\Models\MlmTransaction;
use App\Models\MlmWithdrawalRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'members' => User::query()->where('is_admin', false)->count(),
                'active_packages' => MlmSubscription::query()->where('status', MlmSubscription::STATUS_ACTIVE)->count(),
                'paid_invoices' => (float) MlmInvoice::query()->where('status', MlmInvoice::STATUS_PAID)->sum('amount'),
                'pending_withdrawals' => (float) MlmWithdrawalRequest::query()->where('status', MlmWithdrawalRequest::STATUS_PENDING)->sum('amount'),
                'commissions_paid' => (float) MlmTransaction::query()->whereIn('type', [
                    MlmTransaction::TYPE_DIRECT_BONUS,
                    MlmTransaction::TYPE_LEVEL_BONUS,
                    MlmTransaction::TYPE_BINARY_BONUS,
                ])->sum('amount'),
            ],
            'recentMembers' => User::query()
                ->where('is_admin', false)
                ->with(['referrer', 'subscriptions.plan'])
                ->latest()
                ->take(8)
                ->get(),
            'recentWithdrawals' => MlmWithdrawalRequest::query()
                ->with('user')
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }
}
