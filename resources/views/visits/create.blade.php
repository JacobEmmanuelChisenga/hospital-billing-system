<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Open Visit — {{ $patient->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $patient->type->label() }} · Balance: K {{ number_format((float) $patient->effectiveBalance(), 2) }}</p>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="max-w-2xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
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
                    <select id="visit_type" name="visit_type" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
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
                <textarea id="notes" name="notes" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-6">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-door-open mr-2"></i> Open Visit
                </button>
                <a href="{{ route('patients.show', $patient) }}" class="text-sm text-gray-600 hover:text-gray-900 self-center">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
