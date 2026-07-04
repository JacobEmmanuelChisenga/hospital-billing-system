<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Patient" subtitle="Update details for {{ $patient->name }}." />
    </x-slot>

    <x-flash-messages />

    <div class="card card-body max-w-3xl">
        <form method="POST" action="{{ route('patients.update', $patient) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('patients.partials.form')

            <div class="panel-footer -mx-6 -mb-6 mt-6 flex items-center gap-3 px-6 py-4">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
                <a href="{{ route('patients.show', $patient) }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
