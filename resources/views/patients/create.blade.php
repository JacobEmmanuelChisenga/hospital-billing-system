<x-app-layout>
    <x-slot name="header">
        @php
            $titles = [
                'member' => 'Register Member',
                'dependant' => 'Register Dependant',
                'company' => 'Register Company Patient',
            ];
            $title = $titles[$preselectedType ?? ''] ?? 'Register Patient';
        @endphp
        <x-page-header :title="$title" subtitle="Create a new patient record for the High Cost Section." />
    </x-slot>

    <x-flash-messages />

    <div class="card card-body max-w-3xl">
        <form method="POST" action="{{ route('patients.store') }}" class="space-y-6">
            @csrf

            @include('patients.partials.form')

            <div class="panel-footer -mx-6 -mb-6 mt-6 flex items-center gap-3 px-6 py-4">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Register Patient
                </button>
                <a href="{{ route('patients.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
