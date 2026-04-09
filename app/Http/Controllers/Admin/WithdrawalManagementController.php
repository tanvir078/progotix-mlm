<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProcessWithdrawalRequest;
use App\Models\MlmWithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WithdrawalManagementController extends Controller
{
    public function __construct(
        private readonly WithdrawalService $withdrawalService,
    ) {}

    public function index(Request $request): View
    {
        $status = trim((string) $request->string('status', 'pending'));

        return view('admin.withdrawals', [
            'status' => $status,
            'requests' => MlmWithdrawalRequest::query()
                ->with(['user', 'processor', 'paymentMethod'])
                ->when($status !== 'all', fn ($query) => $query->where('status', $status))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function update(ProcessWithdrawalRequest $request, MlmWithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        $this->withdrawalService->process(
            $request->user(),
            $withdrawalRequest,
            $request->decision(),
            $request->adminNote(),
        );

        return back()->with('status', 'Withdrawal request updated.');
    }
}
