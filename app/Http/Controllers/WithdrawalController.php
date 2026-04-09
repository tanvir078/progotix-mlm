<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mlm\StoreWithdrawalRequest;
use App\Models\MlmPaymentMethod;
use App\Models\MlmWithdrawalRequest;
use App\Services\PaymentMethodService;
use App\Services\WithdrawalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function __construct(
        private readonly WithdrawalService $withdrawalService,
        private readonly PaymentMethodService $paymentMethodService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $pendingTotal = (float) $user->pendingWithdrawalTotal();
        $requestsQuery = $user->withdrawalRequests();
        $payoutMethods = $this->paymentMethodService->availableForUser($user, 'withdrawal');
        $approvedTotal = (float) $user->withdrawalRequests()
            ->where('status', MlmWithdrawalRequest::STATUS_APPROVED)
            ->sum('amount');
        $rejectedCount = (int) $user->withdrawalRequests()
            ->where('status', MlmWithdrawalRequest::STATUS_REJECTED)
            ->count();

        return view('mlm.withdrawals', [
            'walletBalance' => $user->balance,
            'pendingTotal' => $pendingTotal,
            'availableBalance' => max(0, (float) $user->balance - $pendingTotal),
            'withdrawalStats' => [
                'request_count' => (int) $requestsQuery->count(),
                'approved_total' => $approvedTotal,
                'rejected_count' => $rejectedCount,
                'method_count' => $payoutMethods->count(),
            ],
            'payoutMethods' => $payoutMethods,
            'requests' => $requestsQuery
                ->with('paymentMethod')
                ->latest()
                ->paginate(10),
            'paymentTypeLabels' => [
                MlmPaymentMethod::TYPE_E_WALLET => 'E-Wallet',
                MlmPaymentMethod::TYPE_BANK => 'Bank',
                MlmPaymentMethod::TYPE_CARD => 'Card',
                MlmPaymentMethod::TYPE_CRYPTO => 'Crypto',
            ],
        ]);
    }

    public function store(StoreWithdrawalRequest $request): RedirectResponse
    {
        $this->withdrawalService->submit(
            $request->user(),
            $request->paymentMethodId(),
            $request->amount(),
            $request->accountDetails(),
            $request->note(),
        );

        return back()->with('status', 'Withdrawal request submitted.');
    }
}
