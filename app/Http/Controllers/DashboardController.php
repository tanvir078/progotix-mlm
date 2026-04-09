<?php

namespace App\Http\Controllers;

use App\Models\MlmPlan;
use App\Models\MlmProduct;
use App\Models\MlmTransaction;
use App\Models\MlmWithdrawalRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['referrer', 'currentRank']);

        $activeSubscription = $user->activeSubscription();
        $directReferrals = $user->referrals()
            ->withCount('referrals')
            ->latest()
            ->take(6)
            ->get();

        $recentTransactions = $user->transactions()
            ->with('sourceUser')
            ->latest('posted_at')
            ->take(8)
            ->get();

        $plans = MlmPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $directBonusTotal = $user->transactions()
            ->where('type', MlmTransaction::TYPE_DIRECT_BONUS)
            ->sum('amount');

        $binaryBonusTotal = $user->transactions()
            ->where('type', MlmTransaction::TYPE_BINARY_BONUS)
            ->sum('amount');

        $monthlyEarnings = $user->transactions()
            ->where('posted_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $pendingWithdrawals = $user->withdrawalRequests()
            ->where('status', MlmWithdrawalRequest::STATUS_PENDING)
            ->sum('amount');

        $workspaceModules = collect(config('mlm.navigation.member'))
            ->map(fn (array $module): array => [
                'label' => $module['label'],
                'icon' => $module['icon'],
                'route' => route($module['route']),
                'description' => $module['description'] ?? null,
            ]);

        $primaryWorkspaceModules = collect(config('mlm.navigation.member'))
            ->filter(fn (array $module): bool => (bool) ($module['mobile_primary'] ?? false))
            ->take(4)
            ->map(fn (array $module): array => [
                'label' => $module['label'],
                'short_label' => $module['short_label'],
                'icon' => $module['icon'],
                'route' => route($module['route']),
            ]);

        $featuredProducts = MlmProduct::query()
            ->where('is_active', true)
            ->orderByDesc('retail_commission_rate')
            ->take(3)
            ->get();

        return view('mlm.dashboard', [
            'user' => $user,
            'activeSubscription' => $activeSubscription,
            'directReferrals' => $directReferrals,
            'recentTransactions' => $recentTransactions,
            'plans' => $plans,
            'recentInvoices' => $user->invoices()
                ->latest('issued_at')
                ->take(4)
                ->get(),
            'workspaceModules' => $workspaceModules,
            'primaryWorkspaceModules' => $primaryWorkspaceModules,
            'featuredProducts' => $featuredProducts,
            'strategy' => config('mlm.strategy'),
            'stats' => [
                'wallet_balance' => $user->balance,
                'team_size' => $user->teamCount(),
                'binary_team_size' => $user->binaryTeamCount(),
                'direct_referrals' => $user->referrals()->count(),
                'direct_bonus_total' => $directBonusTotal,
                'binary_bonus_total' => $binaryBonusTotal,
                'monthly_earnings' => $monthlyEarnings,
                'pending_withdrawals' => $pendingWithdrawals,
                'retail_sales' => (float) $user->retailSalesTotal(),
            ],
        ]);
    }
}
