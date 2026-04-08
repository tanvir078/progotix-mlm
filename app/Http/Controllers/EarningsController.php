<?php

namespace App\Http\Controllers;

use App\Models\MlmTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EarningsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $transactions = $user->transactions()
            ->with(['sourceUser', 'subscription.plan'])
            ->latest('posted_at')
            ->paginate(12);

        return view('mlm.earnings', [
            'totals' => [
                'wallet_balance' => $user->balance,
                'direct_bonus' => $user->transactions()
                    ->where('type', MlmTransaction::TYPE_DIRECT_BONUS)
                    ->sum('amount'),
                'monthly_earnings' => $user->transactions()
                    ->where('posted_at', '>=', now()->startOfMonth())
                    ->sum('amount'),
                'total_transactions' => $user->transactions()->count(),
            ],
            'transactions' => $transactions,
        ]);
    }
}
