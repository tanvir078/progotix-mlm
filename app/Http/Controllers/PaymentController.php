<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mlm\StoreDepositRequest;
use App\Models\MlmDepositRequest;
use App\Models\MlmPaymentMethod;
use App\Services\DepositService;
use App\Services\PaymentMethodService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly DepositService $depositService,
        private readonly PaymentMethodService $paymentMethodService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $availableMethods = $this->paymentMethodService->availableForUser($user, 'deposit');

        return view('mlm.payments', [
            'availableMethods' => $availableMethods,
            'methodGroups' => $availableMethods->groupBy('type'),
            'depositStats' => [
                'method_count' => $availableMethods->count(),
                'pending_total' => (float) $user->depositRequests()
                    ->where('status', MlmDepositRequest::STATUS_PENDING)
                    ->sum('amount'),
                'approved_total' => (float) $user->depositRequests()
                    ->where('status', MlmDepositRequest::STATUS_APPROVED)
                    ->sum('net_amount'),
                'global_method_count' => $availableMethods->whereNull('country_code')->count(),
            ],
            'requests' => $user->depositRequests()
                ->with(['paymentMethod', 'processor'])
                ->latest('submitted_at')
                ->paginate(10),
            'paymentTypeLabels' => [
                MlmPaymentMethod::TYPE_E_WALLET => 'E-Wallet',
                MlmPaymentMethod::TYPE_BANK => 'Bank',
                MlmPaymentMethod::TYPE_CARD => 'Card',
                MlmPaymentMethod::TYPE_CRYPTO => 'Crypto',
            ],
        ]);
    }

    public function store(StoreDepositRequest $request): RedirectResponse
    {
        $depositRequest = $this->depositService->submit(
            $request->user(),
            $request->paymentMethodId(),
            $request->amount(),
            $request->senderName(),
            $request->senderAccount(),
            $request->transactionReference(),
            $request->paymentProof(),
            $request->note(),
        );

        return redirect()
            ->route('mlm.payments.index')
            ->with('status', 'Deposit request '.$depositRequest->transaction_reference.' submitted for review.');
    }
}
