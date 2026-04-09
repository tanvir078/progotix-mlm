<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlmDocument;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $statuses = [
            MlmDocument::STATUS_PENDING,
            MlmDocument::STATUS_APPROVED,
            MlmDocument::STATUS_REJECTED,
        ];

        return view('admin.documents', [
            'documents' => MlmDocument::query()
                ->with('user')
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($nested) use ($search): void {
                        $nested
                            ->where('document_type', 'like', "%{$search}%")
                            ->orWhere('document_number', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($userQuery) use ($search): void {
                                $userQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('username', 'like', "%{$search}%")
                                    ->orWhere('member_code', 'like', "%{$search}%");
                            });
                    });
                })
                ->when(in_array($status, $statuses, true), fn ($query) => $query->where('status', $status))
                ->latest('submitted_at')
                ->paginate(12)
                ->withQueryString(),
            'search' => $search,
            'status' => $status,
            'statuses' => $statuses,
            'stats' => [
                'pending' => MlmDocument::query()->where('status', MlmDocument::STATUS_PENDING)->count(),
                'approved' => MlmDocument::query()->where('status', MlmDocument::STATUS_APPROVED)->count(),
                'rejected' => MlmDocument::query()->where('status', MlmDocument::STATUS_REJECTED)->count(),
            ],
        ]);
    }

    public function update(Request $request, MlmDocument $document): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in([
                MlmDocument::STATUS_PENDING,
                MlmDocument::STATUS_APPROVED,
                MlmDocument::STATUS_REJECTED,
            ])],
            'notes' => ['nullable', 'string'],
        ]);

        $data['reviewed_at'] = $data['status'] === MlmDocument::STATUS_PENDING ? null : now();

        $document->update($data);

        return back()->with('status', 'Document review updated successfully.');
    }
}
