<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Clinical Notes" :subtitle="$visit->visitNumber() . ' — ' . $visit->patient->name . ' — ' . $visit->visit_date->format('d M Y')" />
    </x-slot>

    <x-flash-messages />

    <div class="card card-body max-w-3xl">
        <form method="POST" action="{{ route('clinical-notes.store', $visit) }}" class="space-y-6">
            @csrf

            @foreach ([
                'complaint' => 'Chief Complaint',
                'vitals' => 'Vital Signs',
                'examination_findings' => 'Examination Findings',
                'diagnosis' => 'Diagnosis',
                'treatment_notes' => 'Treatment Given',
                'procedures_performed' => 'Procedures Performed',
                'follow_up_instructions' => 'Follow-up Instructions',
            ] as $field => $label)
                <div>
                    <x-input-label :for="$field" :value="__($label)" />
                    <textarea id="{{ $field }}" name="{{ $field }}" rows="3"
                        class="form-input mt-1">{{ old($field, $note?->$field) }}</textarea>
                    <x-input-error :messages="$errors->get($field)" class="mt-2" />
                </div>
            @endforeach

            <div class="panel-footer -mx-6 -mb-6 mt-6 flex gap-3 px-6 py-4">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-circle-check"></i> Complete Consultation
                </button>
                <a href="{{ route('visits.show', $visit) }}" class="btn-ghost self-center">Back to Visit</a>
            </div>
        </form>
    </div>
</x-app-layout>
