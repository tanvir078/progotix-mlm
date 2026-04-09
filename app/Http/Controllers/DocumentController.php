<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mlm\StoreDocumentRequest;
use App\Models\MlmDocument;
use App\Services\DocumentSubmissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentSubmissionService $documentSubmissionService,
    ) {}

    public function index(Request $request): View
    {
        $documentsQuery = $request->user()->documents();

        return view('mlm.documents', [
            'documents' => $documentsQuery
                ->latest('submitted_at')
                ->paginate(10),
            'documentStats' => [
                'submitted_count' => (int) $request->user()->documents()->count(),
                'pending_count' => (int) $request->user()->documents()->where('status', MlmDocument::STATUS_PENDING)->count(),
                'approved_count' => (int) $request->user()->documents()->where('status', MlmDocument::STATUS_APPROVED)->count(),
                'rejected_count' => (int) $request->user()->documents()->where('status', MlmDocument::STATUS_REJECTED)->count(),
            ],
            'documentTypes' => [
                'Passport',
                'National ID',
                'Driving License',
                'Trade License',
                'Tax Certificate',
            ],
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $this->documentSubmissionService->submit($request->user(), $request->payload());

        return back()->with('status', 'Document submitted for review.');
    }
}
