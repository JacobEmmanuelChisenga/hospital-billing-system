<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Open Visit — {{ $patient->name }}" subtitle="{{ $patient->type->label() }} · Balance: K {{ number_format((float) $patient->effectiveBalance(), 2) }}" />
    </x-slot>

    <x-flash-messages />

    <div class="card card-body max-w-2xl">
        <form method="POST" action="{{ route('visits.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $patient->id }}">

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="visit_date" :value="__('Visit Date')" />
                    <x-text-input id="visit_date" name="visit_date" type="date" class="mt-1 block w-full"
                        :value="old('visit_date', now()->toDateString())" required />
                    <x-input-error :messages="$errors->get('visit_date')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="visit_type" :value="__('Visit Type')" />
                    <select id="visit_type" name="visit_type" required class="form-input mt-1">
                        @foreach (\App\Enums\VisitType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('visit_type') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('visit_type')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="ward_bed" :value="__('Ward / Bed (IPD only)')" />
                <x-text-input id="ward_bed" name="ward_bed" type="text" class="mt-1 block w-full" :value="old('ward_bed')" />
                <x-input-error :messages="$errors->get('ward_bed')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea id="notes" name="notes" rows="2" class="form-input mt-1">{{ old('notes') }}</textarea>
            </div>

            <div class="panel-footer -mx-6 -mb-6 mt-6 flex gap-3 px-6 py-4">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-door-open"></i> Open Visit
                </button>
                <a href="{{ route('patients.show', $patient) }}" class="btn-ghost self-center">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
