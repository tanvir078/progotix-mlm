<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlmBinaryLedger;
use App\Models\MlmInvoice;
use App\Models\MlmPlan;
use App\Models\MlmSubscription;
use App\Models\MlmTransaction;
use App\Models\MlmWithdrawalRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __invoke(): View
    {
        $driver = DB::connection()->getDriverName();
        $monthExpression = match ($driver) {
            'sqlite' => 'strftime("%Y-%m", issued_at)',
            'pgsql' => "to_char(issued_at, 'YYYY-MM')",
            default => 'DATE_FORMAT(issued_at, "%Y-%m")',
        };

        $monthlyRevenue = MlmInvoice::query()
            ->selectRaw("{$monthExpression} as month, sum(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.reports', [
            'totals' => [
                'members' => User::query()->where('is_admin', false)->count(),
                'binary_nodes' => User::query()->whereNotNull('binary_parent_id')->count(),
                'team_commission' => (float) MlmTransaction::query()
                    ->where('direction', 'credit')
                    ->whereIn('type', [
                        MlmTransaction::TYPE_DIRECT_BONUS,
                        MlmTransaction::TYPE_LEVEL_BONUS,
                        MlmTransaction::TYPE_TEAM_SALES_BONUS,
                    ])->sum('amount'),
                'binary_bonus_paid' => (float) MlmTransaction::query()->where('type', MlmTransaction::TYPE_BINARY_BONUS)->sum('amount'),
                'withdrawal_approved' => (float) MlmWithdrawalRequest::query()->where('status', MlmWithdrawalRequest::STATUS_APPROVED)->sum('amount'),
                'carry_forward' => (float) MlmBinaryLedger::query()->sum(DB::raw('left_carry + right_carry')),
            ],
            'planPerformance' => MlmPlan::query()
                ->leftJoin('mlm_subscriptions', 'mlm_subscriptions.plan_id', '=', 'mlm_plans.id')
                ->select([
                    'mlm_plans.name',
                    'mlm_plans.code',
                    DB::raw('count(mlm_subscriptions.id) as subscriptions_count'),
                    DB::raw('coalesce(sum(mlm_subscriptions.amount), 0) as revenue'),
                ])
                ->groupBy('mlm_plans.id', 'mlm_plans.name', 'mlm_plans.code')
                ->orderByDesc('revenue')
                ->get(),
            'topEarners' => User::query()
                ->where('is_admin', false)
                ->orderByDesc('balance')
                ->take(10)
                ->get(['name', 'username', 'balance']),
            'monthlyRevenue' => $monthlyRevenue,
            'statusBreakdown' => [
                'active_subscriptions' => MlmSubscription::query()->where('status', MlmSubscription::STATUS_ACTIVE)->count(),
                'pending_withdrawals' => MlmWithdrawalRequest::query()->where('status', MlmWithdrawalRequest::STATUS_PENDING)->count(),
                'paid_invoices' => MlmInvoice::query()->where('status', MlmInvoice::STATUS_PAID)->count(),
            ],
        ]);
    }
}
