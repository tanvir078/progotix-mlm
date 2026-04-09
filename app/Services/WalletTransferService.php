<?php

namespace App\Services;

use App\Models\MlmTransaction;
use App\Models\MlmWalletTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletTransferService
{
    public function __construct(
        private readonly RankService $rankService,
    ) {}

    public function feeRate(): float
    {
        return (float) config('mlm.wallet.transfer_fee_rate', 0.02);
    }

    public function transfer(User $sender, User $receiver, float $amount, ?string $note = null): MlmWalletTransfer
    {
        return DB::transaction(function () use ($sender, $receiver, $amount, $note): MlmWalletTransfer {
            /** @var User $lockedSender */
            $lockedSender = User::query()
                ->lockForUpdate()
                ->findOrFail($sender->id);
            /** @var User $lockedReceiver */
            $lockedReceiver = User::query()
                ->lockForUpdate()
                ->findOrFail($receiver->id);

            if ($lockedSender->is($lockedReceiver)) {
                throw ValidationException::withMessages([
                    'receiver_identity' => 'You cannot transfer to your own account.',
                ]);
            }

            $fee = round($amount * $this->feeRate(), 2);
            $netAmount = round($amount - $fee, 2);
            $pendingTotal = (float) $lockedSender->pendingWithdrawalTotal();
            $availableBalance = max(0, (float) $lockedSender->balance - $pendingTotal);
            $totalDebit = round($amount + $fee, 2);

            if ($totalDebit > $availableBalance) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient transferable balance after fee.',
                ]);
            }

            $transfer = MlmWalletTransfer::query()->create([
                'sender_id' => $lockedSender->id,
                'receiver_id' => $lockedReceiver->id,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'currency' => 'USD',
                'note' => $note,
                'transferred_at' => now(),
            ]);

            $lockedSender->transactions()->create([
                'source_user_id' => $lockedReceiver->id,
                'type' => MlmTransaction::TYPE_WALLET_TRANSFER_OUT,
                'direction' => 'debit',
                'amount' => $amount,
                'title' => 'Wallet transfer sent',
                'note' => 'Transfer sent to @'.$lockedReceiver->username,
                'posted_at' => now(),
            ]);

            if ($fee > 0) {
                $lockedSender->transactions()->create([
                    'source_user_id' => $lockedReceiver->id,
                    'type' => MlmTransaction::TYPE_WALLET_TRANSFER_FEE,
                    'direction' => 'debit',
                    'amount' => $fee,
                    'title' => 'Wallet transfer fee',
                    'note' => 'Transfer fee charged on internal fund movement.',
                    'posted_at' => now(),
                ]);
            }

            $lockedReceiver->transactions()->create([
                'source_user_id' => $lockedSender->id,
                'type' => MlmTransaction::TYPE_WALLET_TRANSFER_IN,
                'direction' => 'credit',
                'amount' => $netAmount,
                'title' => 'Wallet transfer received',
                'note' => 'Transfer received from @'.$lockedSender->username,
                'posted_at' => now(),
            ]);

            $lockedSender->decrement('balance', $totalDebit);
            $lockedReceiver->increment('balance', $netAmount);

            $this->rankService->sync($lockedSender);
            $this->rankService->sync($lockedReceiver);

            return $transfer;
        });
    }
}
