<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Add Company Account" subtitle="Create a company before the Records Clerk links company-sponsored patients." />
    </x-slot>

    <x-flash-messages />

    <div class="card card-body max-w-2xl">
        <form method="POST" action="{{ route('company-accounts.store') }}" class="space-y-6">
            @csrf

            <div>
                <x-input-label for="name" :value="__('Company Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="contact_person" :value="__('Contact Person')" />
                <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full" :value="old('contact_person')" />
                <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="phone" :value="__('Phone')" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea id="notes" name="notes" rows="3" class="form-input mt-1">{{ old('notes') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
            </div>

            <div class="panel-footer -mx-6 -mb-6 mt-6 flex items-center gap-3 px-6 py-4">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-building"></i> Create Company
                </button>
                <a href="{{ route('company-accounts.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
