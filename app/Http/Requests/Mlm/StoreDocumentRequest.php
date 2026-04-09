<?php

namespace App\Http\Requests\Mlm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'max:100'],
            'document_number' => ['nullable', 'string', 'max:120'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'document_file' => ['nullable', 'file', 'max:4096', 'mimes:jpg,jpeg,png,pdf'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'document_type' => trim((string) $this->input('document_type')),
            'document_number' => $this->filled('document_number') ? trim((string) $this->input('document_number')) : null,
            'country_code' => $this->filled('country_code') ? strtoupper(trim((string) $this->input('country_code'))) : null,
            'notes' => $this->filled('notes') ? trim((string) $this->input('notes')) : null,
        ]);
    }

    /**
     * @return array{document_type:string,document_number:?string,country_code:?string,document_file:?UploadedFile,notes:?string}
     */
    public function payload(): array
    {
        /** @var UploadedFile|null $documentFile */
        $documentFile = $this->file('document_file');

        return [
            'document_type' => (string) $this->validated('document_type'),
            'document_number' => $this->validated('document_number'),
            'country_code' => $this->validated('country_code'),
            'document_file' => $documentFile,
            'notes' => $this->validated('notes'),
        ];
    }
}
