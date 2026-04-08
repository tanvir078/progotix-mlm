<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlmTransaction;
use App\Models\MlmWithdrawalRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalManagementController extends Controller
{
    public function index(Request $request): View
    {
        $status = trim((string) $request->string('status', 'pending'));

        return view('admin.withdrawals', [
            'status' => $status,
            'requests' => MlmWithdrawalRequest::query()
                ->with(['user', 'processor'])
                ->when($status !== 'all', fn ($query) => $query->where('status', $status))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function update(Request $request, MlmWithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($withdrawalRequest->status !== MlmWithdrawalRequest::STATUS_PENDING) {
            return back()->with('status', 'This withdrawal request has already been processed.');
        }

        if ($validated['decision'] === 'approve' && (float) $withdrawalRequest->user->balance < (float) $withdrawalRequest->amount) {
            return back()->withErrors([
                'decision' => 'Insufficient wallet balance to approve this withdrawal.',
            ]);
        }

        DB::transaction(function () use ($request, $withdrawalRequest, $validated): void {
            $withdrawalRequest->forceFill([
                'status' => $validated['decision'] === 'approve'
                    ? MlmWithdrawalRequest::STATUS_APPROVED
                    : MlmWithdrawalRequest::STATUS_REJECTED,
                'admin_note' => $validated['admin_note'] ?? null,
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
            ])->save();

            if ($validated['decision'] === 'approve') {
                $withdrawalRequest->user->decrement('balance', $withdrawalRequest->amount);

                $withdrawalRequest->user->transactions()->create([
                    'type' => MlmTransaction::TYPE_WITHDRAWAL,
                    'direction' => 'debit',
                    'amount' => $withdrawalRequest->amount,
                    'title' => 'Withdrawal approved',
                    'note' => 'Admin approved a withdrawal request.',
                    'posted_at' => now(),
                ]);
            }
        });

        return back()->with('status', 'Withdrawal request updated.');
    }
}
