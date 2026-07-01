<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Open Visit</h2>
            <p class="mt-1 text-sm text-gray-500">Select a patient to start a new visit.</p>
        </div>
    </x-slot>

    <div class="max-w-2xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('visits.create') }}" class="space-y-4">
            <div>
                <x-input-label for="patient_id" :value="__('Patient')" />
                <select id="patient_id" name="patient_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
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
            <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                Continue
            </button>
        </form>
    </div>
</x-app-layout>
