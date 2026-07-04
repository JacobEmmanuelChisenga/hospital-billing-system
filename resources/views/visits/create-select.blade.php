<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Open Visit" subtitle="Select a patient to start a new visit." />
    </x-slot>

    <div class="card card-body max-w-2xl">
        <form method="GET" action="{{ route('visits.create') }}" class="space-y-4">
            <div>
                <x-input-label for="patient_id" :value="__('Patient')" />
                <select id="patient_id" name="patient_id" required class="form-input mt-1">
                    <option value="">Select patient...</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}">
                            {{ $patient->name }}
                            @if ($patient->hc_number) ({{ $patient->hc_number }}) @endif
                            — {{ $patient->type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">Continue</button>
        </form>
    </div>
</x-app-layout>
