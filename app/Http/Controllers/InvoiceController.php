<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('mlm.invoices', [
            'summary' => [
                'paid_total' => $user->invoices()->where('status', 'paid')->sum('amount'),
                'invoice_count' => $user->invoices()->count(),
            ],
            'invoices' => $user->invoices()
                ->with('subscription.plan')
                ->latest('issued_at')
                ->paginate(10),
        ]);
    }
}
