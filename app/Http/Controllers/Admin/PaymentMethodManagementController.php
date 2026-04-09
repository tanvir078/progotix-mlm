<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertPaymentMethodRequest;
use App\Models\MlmPaymentMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PaymentMethodManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.payment-methods', [
            'methods' => MlmPaymentMethod::query()
                ->orderByDesc('is_active')
                ->orderBy('sort_order')
                ->paginate(12),
            'stats' => [
                'active_count' => MlmPaymentMethod::query()->where('is_active', true)->count(),
                'deposit_enabled' => MlmPaymentMethod::query()->where('supports_deposit', true)->count(),
                'withdrawal_enabled' => MlmPaymentMethod::query()->where('supports_withdrawal', true)->count(),
                'country_scoped' => MlmPaymentMethod::query()->whereNotNull('country_code')->count(),
            ],
            'types' => [
                MlmPaymentMethod::TYPE_E_WALLET,
                MlmPaymentMethod::TYPE_BANK,
                MlmPaymentMethod::TYPE_CARD,
                MlmPaymentMethod::TYPE_CRYPTO,
            ],
        ]);
    }

    public function store(UpsertPaymentMethodRequest $request): RedirectResponse
    {
        MlmPaymentMethod::query()->create($request->payload());

        return back()->with('status', 'Payment method created successfully.');
    }

    public function update(UpsertPaymentMethodRequest $request, MlmPaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update($request->payload());

        return back()->with('status', 'Payment method updated successfully.');
    }

    public function destroy(MlmPaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->delete();

        return back()->with('status', 'Payment method deleted successfully.');
    }
}
