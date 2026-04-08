<?php

namespace App\Http\Controllers;

use App\Models\MlmInvoice;
use App\Services\SimplePdfService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfController extends Controller
{
    public function __construct(
        private readonly SimplePdfService $pdfService,
    ) {
    }

    public function __invoke(Request $request, MlmInvoice $invoice): Response
    {
        abort_unless($request->user()->is_admin || $invoice->user_id === $request->user()->id, 403);

        $invoice->loadMissing(['user', 'subscription.plan']);

        $pdf = $this->pdfService->makeDocument('ProgotiX Invoice', [
            "Invoice No: {$invoice->invoice_no}",
            "Member: {$invoice->user->name} (@{$invoice->user->username})",
            'Package: '.($invoice->subscription?->plan?->name ?? 'N/A'),
            'Status: '.strtoupper($invoice->status),
            'Amount: BDT '.number_format((float) $invoice->amount, 2),
            'Issued: '.$invoice->issued_at?->format('Y-m-d H:i'),
            'Paid: '.($invoice->paid_at?->format('Y-m-d H:i') ?? 'N/A'),
            'Notes: '.($invoice->notes ?: 'No notes'),
        ]);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$invoice->invoice_no.'.pdf"',
        ]);
    }
}
