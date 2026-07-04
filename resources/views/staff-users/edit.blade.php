<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Staff User" subtitle="{{ $staffUser->name }} — {{ $staffUser->email }}">
            <x-slot name="actions">
                <a href="{{ route('staff-users.index') }}" class="btn-ghost">&larr; Staff Users</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <div class="card card-body max-w-2xl">
        <form method="POST" action="{{ route('staff-users.update', $staffUser) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $staffUser->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email Address')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $staffUser->email)" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-input-label for="role" :value="__('Role')" />
                    <select id="role" name="role" required class="form-input mt-1"
                        @disabled($staffUser->id === Auth::id())>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role', $staffUser->role->value) === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    @if ($staffUser->id === Auth::id())
                        <input type="hidden" name="role" value="{{ $staffUser->role->value }}">
                        <p class="form-hint mt-1">You cannot change your own role.</p>
                    @endif
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" required class="form-input mt-1"
                        @disabled($staffUser->id === Auth::id())>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $staffUser->status->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    @if ($staffUser->id === Auth::id())
                        <input type="hidden" name="status" value="{{ $staffUser->status->value }}">
                        <p class="form-hint mt-1">You cannot deactivate your own account.</p>
                    @endif
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
            </div>

            <div class="card card-body bg-slate-50">
                <h3 class="section-title">Reset Password</h3>
                <p class="section-subtitle">Leave blank to keep the current password.</p>
                <div class="mt-4 grid gap-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="password" :value="__('New Password')" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                    </div>
                </div>
            </div>

            <div class="panel-footer -mx-6 -mb-6 mt-6 flex items-center gap-3 px-6 py-4">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
                <a href="{{ route('staff-users.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
