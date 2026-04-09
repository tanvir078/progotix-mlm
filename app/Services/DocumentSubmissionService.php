<?php

namespace App\Services;

use App\Models\MlmDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DocumentSubmissionService
{
    /**
     * @param  array{document_type:string,document_number:?string,country_code:?string,document_file:?UploadedFile,notes:?string}  $payload
     */
    public function submit(User $user, array $payload): MlmDocument
    {
        $path = $payload['document_file']?->store('member-documents', 'public');

        return DB::transaction(function () use ($user, $payload, $path): MlmDocument {
            return $user->documents()->create([
                'document_type' => $payload['document_type'],
                'document_number' => $payload['document_number'],
                'country_code' => $payload['country_code'] ?? $user->country_code,
                'file_path' => $path,
                'status' => MlmDocument::STATUS_PENDING,
                'notes' => $payload['notes'],
                'submitted_at' => now(),
            ]);
        });
    }
}
