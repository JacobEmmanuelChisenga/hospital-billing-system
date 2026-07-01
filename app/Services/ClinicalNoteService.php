<?php

namespace App\Services;

use App\Enums\AuditActionType;
use App\Enums\VisitStatus;
use App\Models\ClinicalNote;
use App\Models\User;
use App\Models\Visit;
use InvalidArgumentException;

/**
 * Nurse workflow: record clinical notes on open patient visits.
 */
class ClinicalNoteService
{
    public function record(Visit $visit, array $data, User $user): ClinicalNote
    {
        if (! $visit->canRecordClinicalNotes()) {
            throw new InvalidArgumentException('Clinical notes can only be recorded after Accounts clears the patient for consultation.');
        }

        $note = ClinicalNote::query()->updateOrCreate(
            ['visit_id' => $visit->id],
            [
                'recorded_by' => $user->id,
                'complaint' => $data['complaint'] ?? null,
                'vitals' => $data['vitals'] ?? null,
                'examination_findings' => $data['examination_findings'] ?? null,
                'diagnosis' => $data['diagnosis'] ?? null,
                'treatment_notes' => $data['treatment_notes'] ?? null,
                'procedures_performed' => $data['procedures_performed'] ?? null,
                'follow_up_instructions' => $data['follow_up_instructions'] ?? null,
            ],
        );

        AuditLogger::log(
            AuditActionType::ClinicalNoteRecorded,
            "Recorded clinical notes for visit #{$visit->id} ({$visit->patient->name}).",
            $note,
            ['visit_id' => $visit->id],
        );

        $visit->update(['status' => VisitStatus::AwaitingBilling]);

        return $note->load(['visit.patient', 'recordedBy']);
    }
}
