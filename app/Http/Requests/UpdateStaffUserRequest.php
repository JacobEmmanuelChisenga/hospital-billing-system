<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateStaffUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() ?? false;
    }

    public function rules(): array
    {
        /** @var \App\Models\User $staffUser */
        $staffUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staffUser->id)],
            'role' => ['required', Rule::enum(UserRole::class)->only(UserRole::assignableCases())],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var \App\Models\User $staffUser */
            $staffUser = $this->route('user');
            $currentUser = $this->user();

            if ($staffUser->id !== $currentUser->id) {
                return;
            }

            if ($this->input('status') === UserStatus::Inactive->value) {
                $validator->errors()->add('status', 'You cannot deactivate your own account.');
            }

            if ($this->input('role') !== UserRole::Administrator->value) {
                $validator->errors()->add('role', 'You cannot change your own role.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'password' => 'new password',
            'password_confirmation' => 'password confirmation',
        ];
    }
}
