<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Add Staff User" subtitle="Create a new staff account with role and initial password." />
    </x-slot>

    <x-flash-messages />

    <div class="card card-body max-w-2xl">
        <form method="POST" action="{{ route('staff-users.store') }}" class="space-y-6">
            @csrf

            <div>
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email Address')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="role" :value="__('Role')" />
                    <select id="role" name="role" required class="form-input mt-1">
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" required class="form-input mt-1">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', 'active') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                </div>
            </div>

            <div class="panel-footer -mx-6 -mb-6 mt-6 flex items-center gap-3 px-6 py-4">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-user-plus"></i> Create Staff User
                </button>
                <a href="{{ route('staff-users.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
