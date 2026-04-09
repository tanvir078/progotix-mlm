<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlmDocument;
use App\Models\MlmInvoice;
use App\Models\MlmOrder;
use App\Models\MlmProduct;
use App\Models\MlmRankAchievement;
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
                'active_products' => MlmProduct::query()->where('is_active', true)->count(),
                'paid_orders' => MlmOrder::query()->where('status', MlmOrder::STATUS_PAID)->count(),
                'paid_invoices' => (float) MlmInvoice::query()->where('status', MlmInvoice::STATUS_PAID)->sum('amount'),
                'pending_withdrawals' => (float) MlmWithdrawalRequest::query()->where('status', MlmWithdrawalRequest::STATUS_PENDING)->sum('amount'),
                'pending_documents' => MlmDocument::query()->where('status', MlmDocument::STATUS_PENDING)->count(),
                'rank_achievements' => MlmRankAchievement::query()->count(),
                'commissions_paid' => (float) MlmTransaction::query()
                    ->where('direction', 'credit')
                    ->whereIn('type', MlmTransaction::commissionCreditTypes())
                    ->sum('amount'),
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
            'recentOrders' => MlmOrder::query()
                ->with(['user', 'items'])
                ->latest('placed_at')
                ->take(6)
                ->get(),
            'pendingDocuments' => MlmDocument::query()
                ->with('user')
                ->where('status', MlmDocument::STATUS_PENDING)
                ->latest('submitted_at')
                ->take(6)
                ->get(),
            'adminModules' => collect(config('mlm.navigation.admin'))
                ->map(fn (array $module): array => [
                    'label' => $module['label'],
                    'description' => $module['description'],
                    'route' => route($module['route']),
                ]),
        ]);
    }
}
