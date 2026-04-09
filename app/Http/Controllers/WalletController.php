<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mlm\TransferWalletRequest;
use App\Models\MlmPaymentMethod;
use App\Models\User;
use App\Services\PaymentMethodService;
use App\Services\WalletTransferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private readonly WalletTransferService $walletTransferService,
        private readonly PaymentMethodService $paymentMethodService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $pendingTotal = (float) $user->pendingWithdrawalTotal();
        $availableBalance = max(0, (float) $user->balance - $pendingTotal);
        $sentTransfers = $user->walletTransfersSent();
        $receivedTransfers = $user->walletTransfersReceived();
        $paymentChannels = $this->paymentMethodService->availableForUser($user);

        $currencies = collect(config('mlm.wallet.supported_currencies'))
            ->map(fn (array $currency): array => [
                ...$currency,
                'converted' => round($availableBalance * (float) $currency['rate'], 2),
            ]);

        return view('mlm.wallet', [
            'walletBalance' => (float) $user->balance,
            'pendingTotal' => $pendingTotal,
            'availableBalance' => $availableBalance,
            'feeRate' => $this->walletTransferService->feeRate(),
            'paymentChannels' => $paymentChannels,
            'currencies' => $currencies,
            'transferStats' => [
                'outgoing_count' => $sentTransfers->count(),
                'outgoing_volume' => (float) $user->walletTransfersSent()->sum('amount'),
                'incoming_volume' => (float) $user->walletTransfersReceived()->sum('net_amount'),
                'currency_count' => $currencies->count(),
                'payment_channel_count' => $paymentChannels->count(),
                'global_channel_count' => $paymentChannels->whereNull('country_code')->count(),
            ],
            'recentTransfers' => $sentTransfers
                ->with('receiver')
                ->latest('transferred_at')
                ->take(8)
                ->get(),
            'recentIncomingTransfers' => $receivedTransfers
                ->with('sender')
                ->latest('transferred_at')
                ->take(8)
                ->get(),
            'paymentTypeLabels' => [
                MlmPaymentMethod::TYPE_E_WALLET => 'E-Wallet',
                MlmPaymentMethod::TYPE_BANK => 'Bank',
                MlmPaymentMethod::TYPE_CARD => 'Card',
                MlmPaymentMethod::TYPE_CRYPTO => 'Crypto',
            ],
        ]);
    }

    public function transfer(TransferWalletRequest $request): RedirectResponse
    {
        $receiver = User::query()
            ->where('username', strtolower($request->receiverIdentity()))
            ->orWhere('member_code', strtoupper($request->receiverIdentity()))
            ->firstOrFail();

        $this->walletTransferService->transfer(
            $request->user(),
            $receiver,
            $request->amount(),
            $request->note(),
        );

        return back()->with('status', 'Wallet transfer sent successfully.');
    }
}
