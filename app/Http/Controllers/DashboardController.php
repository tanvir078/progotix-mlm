<?php

namespace App\Http\Controllers;

use App\Models\MlmPlan;
use App\Models\MlmTransaction;
use App\Models\MlmWithdrawalRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('referrer');

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
            'stats' => [
                'wallet_balance' => $user->balance,
                'team_size' => $user->teamCount(),
                'binary_team_size' => $user->binaryTeamCount(),
                'direct_referrals' => $user->referrals()->count(),
                'direct_bonus_total' => $directBonusTotal,
                'binary_bonus_total' => $binaryBonusTotal,
                'monthly_earnings' => $monthlyEarnings,
                'pending_withdrawals' => $pendingWithdrawals,
            ],
        ]);
    }
}
