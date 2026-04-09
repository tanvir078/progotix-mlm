<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProcessDepositRequest;
use App\Models\MlmDepositRequest;
use App\Models\MlmPaymentMethod;
use App\Services\DepositService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DepositManagementController extends Controller
{
    public function __construct(
        private readonly DepositService $depositService,
    ) {}

    public function index(Request $request): View
    {
        $status = trim((string) $request->string('status', 'pending'));
        $search = trim((string) $request->string('search'));
        $type = trim((string) $request->string('type'));

        $requests = MlmDepositRequest::query()
            ->with(['user', 'paymentMethod', 'processor'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($type !== '', fn ($query) => $query->where('payment_method_type', $type))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('transaction_reference', 'like', "%{$search}%")
                        ->orWhere('payment_method_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%")
                                ->orWhere('member_code', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('submitted_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.deposits', [
            'status' => $status,
            'search' => $search,
            'type' => $type,
            'types' => [
                MlmPaymentMethod::TYPE_E_WALLET,
                MlmPaymentMethod::TYPE_BANK,
                MlmPaymentMethod::TYPE_CARD,
                MlmPaymentMethod::TYPE_CRYPTO,
            ],
            'requests' => $requests,
            'stats' => [
                'pending_total' => (float) MlmDepositRequest::query()->where('status', MlmDepositRequest::STATUS_PENDING)->sum('amount'),
                'approved_total' => (float) MlmDepositRequest::query()->where('status', MlmDepositRequest::STATUS_APPROVED)->sum('net_amount'),
                'pending_count' => MlmDepositRequest::query()->where('status', MlmDepositRequest::STATUS_PENDING)->count(),
                'proof_count' => MlmDepositRequest::query()->whereNotNull('payment_proof_path')->count(),
            ],
        ]);
    }

    public function update(ProcessDepositRequest $request, MlmDepositRequest $depositRequest): RedirectResponse
    {
        $this->depositService->process(
            $request->user(),
            $depositRequest,
            $request->decision(),
            $request->adminNote(),
        );

        return back()->with('status', 'Deposit request updated successfully.');
    }
}
