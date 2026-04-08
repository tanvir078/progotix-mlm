<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlmWithdrawalRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayoutExportController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        $fileName = 'withdrawal-export-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['ID', 'Member', 'Username', 'Amount', 'Method', 'Status', 'Requested At', 'Processed At']);

            MlmWithdrawalRequest::query()
                ->with('user')
                ->latest()
                ->chunk(200, function ($requests) use ($handle): void {
                    foreach ($requests as $request) {
                        fputcsv($handle, [
                            $request->id,
                            $request->user->name,
                            $request->user->username,
                            number_format((float) $request->amount, 2, '.', ''),
                            $request->payment_method,
                            $request->status,
                            $request->created_at?->format('Y-m-d H:i:s'),
                            $request->processed_at?->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
