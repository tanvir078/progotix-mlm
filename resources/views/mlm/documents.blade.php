<x-layouts::app :title="__('Documents')">
    @php
        $documentCards = [
            [
                'label' => 'Submitted',
                'value' => number_format((int) $documentStats['submitted_count']),
                'meta' => 'All uploads',
                'icon' => 'folder-open',
                'tone' => 'brand',
            ],
            [
                'label' => 'Pending',
                'value' => number_format((int) $documentStats['pending_count']),
                'meta' => 'Waiting review',
                'icon' => 'clock',
                'tone' => 'accent',
            ],
            [
                'label' => 'Approved',
                'value' => number_format((int) $documentStats['approved_count']),
                'meta' => 'Verified docs',
                'icon' => 'check-badge',
            ],
            [
                'label' => 'Rejected',
                'value' => number_format((int) $documentStats['rejected_count']),
                'meta' => 'Needs fix',
                'icon' => 'x-circle',
            ],
        ];
    @endphp

    <x-app-page spacing="loose">
        @if (session('status'))
            <div class="app-alert app-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="app-alert app-alert-danger">
                <p class="font-medium">Document submission review করুন।</p>
                <p class="mt-1 text-sm">{{ $errors->first() }}</p>
            </div>
        @endif

        <x-app-panel variant="hero" padding="hero">
            <div class="grid gap-6 xl:grid-cols-[1.12fr_0.88fr] xl:items-end">
                <div class="space-y-4">
                    <p class="app-kicker">Identity & Trust Desk</p>
                    <div class="space-y-4">
                        <h1 class="max-w-3xl text-3xl font-semibold tracking-tight text-white sm:text-4xl xl:text-[2.6rem]">
                            Optional document center for KYC support, payout trust signals, and member verification readiness.
                        </h1>
                        <p class="max-w-2xl text-sm leading-7 text-white/82 sm:text-base">
                            এই screen-এ identity document submit, review status track, আর verification-related history mobile-friendly way-এ রাখা হয়েছে।
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="rounded-[1.5rem] border border-white/12 bg-white/10 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.3em] text-white/62">Suggested Types</p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            @foreach ($documentTypes as $type)
                                <span class="app-pill app-pill-primary">{{ $type }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] border border-white/12 bg-black/15 p-4 backdrop-blur sm:p-5">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/58">Review Flow</p>
                        <p class="mt-3 text-sm leading-6 text-white/76">
                            Submit -> Pending Review -> Approved / Rejected
                        </p>
                    </div>
                </div>
            </div>
        </x-app-panel>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($documentCards as $card)
                <x-app-stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :meta="$card['meta']"
                    :icon="$card['icon']"
                    :tone="$card['tone'] ?? 'default'"
                />
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.94fr_1.06fr]">
            <x-app-panel>
                <x-app-section-heading
                    title="Optional Documents / KYC"
                    description="Submit optional documents for verification, payout support, or account trust signals."
                    eyebrow="Submit"
                />

                <form method="POST" action="{{ route('mlm.documents.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label class="app-form-label">Document Type</label>
                        <input
                            name="document_type"
                            list="document-types"
                            type="text"
                            value="{{ old('document_type') }}"
                            placeholder="Passport, National ID, Trade License"
                            class="app-form-control"
                        />
                        <datalist id="document-types">
                            @foreach ($documentTypes as $type)
                                <option value="{{ $type }}"></option>
                            @endforeach
                        </datalist>
                        @error('document_type')
                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="app-form-label">Document Number</label>
                            <input name="document_number" type="text" value="{{ old('document_number') }}" class="app-form-control" />
                        </div>

                        <div>
                            <label class="app-form-label">Country Code</label>
                            <input
                                name="country_code"
                                type="text"
                                value="{{ old('country_code', auth()->user()->country_code) }}"
                                class="app-form-control uppercase"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="app-form-label">Document File</label>
                        <input name="document_file" type="file" class="app-form-control" />
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Accepted: JPG, PNG, PDF up to 4MB.</p>
                        @error('document_file')
                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="app-form-label">Notes</label>
                        <textarea name="notes" rows="3" class="app-form-control" placeholder="Optional review note">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="app-button-primary w-full">
                        Submit document
                    </button>
                </form>
            </x-app-panel>

            <div class="grid gap-6">
                <x-app-panel variant="contrast">
                    <x-app-section-heading
                        title="Verification Notes"
                        description="How documents help trust, payout support, and compliance-friendly operations."
                        eyebrow="Guidance"
                    />

                    <div class="mt-5 app-list-stack">
                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Documents support trust signals</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    Member profile credibility বাড়াতে verified document useful।
                                </p>
                            </div>
                        </div>
                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Rejected docs stay traceable</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    ভুল file বা mismatch হলে rejected status history-তে দেখা যাবে।
                                </p>
                            </div>
                        </div>
                        <div class="app-list-row">
                            <div>
                                <p class="font-semibold text-zinc-950 dark:text-white">Uploads are optional for now</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    Current flow optional, but payout review বা higher trust workflow-এ helpful।
                                </p>
                            </div>
                        </div>
                    </div>
                </x-app-panel>

                <x-app-panel>
                    <x-app-section-heading
                        title="Submission History"
                        description="Every uploaded document with type, status, and review timing."
                        eyebrow="History"
                    />

                    <div class="mt-5 app-list-stack">
                        @forelse ($documents as $document)
                            @php
                                $statusClass = match ($document->status) {
                                    'approved' => 'app-status-badge app-status-badge-success',
                                    'rejected' => 'app-status-badge app-status-badge-danger',
                                    default => 'app-status-badge app-status-badge-warning',
                                };
                            @endphp
                            <div class="app-list-row">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="font-semibold text-zinc-950 dark:text-white">{{ $document->document_type }}</p>
                                        <span class="{{ $statusClass }}">{{ $document->status }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $document->document_number ?: 'No document number' }}</p>
                                    <p class="text-xs uppercase tracking-[0.22em] text-zinc-400">
                                        Submitted {{ $document->submitted_at?->format('d M Y, h:i A') ?? $document->created_at->format('d M Y, h:i A') }}
                                    </p>
                                    @if ($document->notes)
                                        <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $document->notes }}</p>
                                    @endif
                                </div>

                                <div class="text-left sm:text-right">
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $document->country_code ?: 'N/A' }}</p>
                                    @if ($document->reviewed_at)
                                        <p class="mt-2 text-xs uppercase tracking-[0.22em] text-zinc-400">
                                            Reviewed {{ $document->reviewed_at->format('d M Y') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="app-empty-state">
                                No documents submitted yet.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6">{{ $documents->links() }}</div>
                </x-app-panel>
            </div>
        </section>
    </x-app-page>
</x-layouts::app>
