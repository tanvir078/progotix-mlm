<?php

namespace App\Http\Controllers;

use App\Models\MlmWithdrawalRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $pendingTotal = (float) $user->pendingWithdrawalTotal();

        return view('mlm.withdrawals', [
            'walletBalance' => $user->balance,
            'pendingTotal' => $pendingTotal,
            'availableBalance' => max(0, (float) $user->balance - $pendingTotal),
            'requests' => $user->withdrawalRequests()
                ->latest()
                ->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $pendingTotal = (float) $user->pendingWithdrawalTotal();
        $availableBalance = max(0, (float) $user->balance - $pendingTotal);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:'.$availableBalance],
            'payment_method' => ['required', 'string', 'max:120'],
            'account_details' => ['required', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $user->withdrawalRequests()->create([
            ...$validated,
            'status' => MlmWithdrawalRequest::STATUS_PENDING,
        ]);

        return back()->with('status', 'Withdrawal request submitted.');
    }
}
