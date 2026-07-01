<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Clinical Notes</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $visit->patient->name }} — {{ $visit->visit_date->format('d M Y') }}</p>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="max-w-3xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
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
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">{{ old($field, $note?->$field) }}</textarea>
                    <x-input-error :messages="$errors->get($field)" class="mt-2" />
                </div>
            @endforeach

            <div class="flex gap-3 border-t border-gray-100 pt-6">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-notes-medical mr-2"></i> Save Clinical Notes
                </button>
                <a href="{{ route('visits.show', $visit) }}" class="text-sm text-gray-600 hover:text-gray-900 self-center">Back to Visit</a>
            </div>
        </form>
    </div>
</x-app-layout>
