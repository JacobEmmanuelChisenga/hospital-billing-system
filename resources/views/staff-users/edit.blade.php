<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Edit Staff User</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $staffUser->name }} — {{ $staffUser->email }}</p>
            </div>
            <a href="{{ route('staff-users.index') }}" class="text-sm text-hospital-700 hover:underline">&larr; Staff Users</a>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="max-w-2xl rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
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
                    <select id="role" name="role" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500"
                        @disabled($staffUser->id === Auth::id())>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role', $staffUser->role->value) === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    @if ($staffUser->id === Auth::id())
                        <input type="hidden" name="role" value="{{ $staffUser->role->value }}">
                        <p class="mt-1 text-xs text-gray-500">You cannot change your own role.</p>
                    @endif
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500"
                        @disabled($staffUser->id === Auth::id())>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $staffUser->status->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    @if ($staffUser->id === Auth::id())
                        <input type="hidden" name="status" value="{{ $staffUser->status->value }}">
                        <p class="mt-1 text-xs text-gray-500">You cannot deactivate your own account.</p>
                    @endif
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <h3 class="text-sm font-semibold text-gray-800">Reset Password</h3>
                <p class="mt-1 text-xs text-gray-500">Leave blank to keep the current password.</p>
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

            <div class="flex items-center gap-3 border-t border-gray-100 pt-6">
                <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-save mr-2"></i> Save Changes
                </button>
                <a href="{{ route('staff-users.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
