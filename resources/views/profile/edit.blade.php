<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Profile" subtitle="Update your account information and password." />
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <div class="card card-body">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card card-body">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card card-body">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
