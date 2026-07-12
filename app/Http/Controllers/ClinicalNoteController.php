<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClinicalNoteRequest;
use App\Models\Visit;
use App\Services\ClinicalNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClinicalNoteController extends Controller
{
    public function __construct(
        private ClinicalNoteService $clinicalNoteService,
    ) {}

    public function edit(Visit $visit): View|RedirectResponse
    {
        if (! $visit->canRecordClinicalNotes()) {
            return redirect()
                ->route('visits.show', $visit)
                ->with('error', 'Clinical notes can only be edited after Accounts clears the patient for consultation.');
        }

        $visit->load(['patient', 'clinicalNote']);

        return view('clinical-notes.edit', [
            'visit' => $visit,
            'note' => $visit->clinicalNote,
        ]);
    }

    public function store(StoreClinicalNoteRequest $request, Visit $visit): RedirectResponse
    {
        $this->clinicalNoteService->record($visit, $request->validated(), $request->user());

        return redirect()
            ->route('consultant.queue')
            ->with('success', 'Consultation saved. Visit is now awaiting billing.');
    }
}
