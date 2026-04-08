<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlmInvoice;
use Illuminate\Contracts\View\View;

class InvoiceManagementController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.invoices', [
            'summary' => [
                'paid_total' => (float) MlmInvoice::query()->where('status', MlmInvoice::STATUS_PAID)->sum('amount'),
                'invoice_count' => MlmInvoice::query()->count(),
            ],
            'invoices' => MlmInvoice::query()
                ->with(['user', 'subscription.plan'])
                ->latest('issued_at')
                ->paginate(15),
        ]);
    }
}
