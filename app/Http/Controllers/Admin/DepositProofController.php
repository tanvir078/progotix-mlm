<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlmDepositRequest;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepositProofController extends Controller
{
    public function __invoke(MlmDepositRequest $depositRequest): StreamedResponse
    {
        abort_unless(
            $depositRequest->payment_proof_path && Storage::disk('local')->exists($depositRequest->payment_proof_path),
            404,
        );

        return Storage::disk('local')->download($depositRequest->payment_proof_path);
    }
}
