<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'section' => ['required', 'string', 'max:255'],
            'system_name' => ['required', 'string', 'max:255'],
            'session_lifetime_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'large_deposit_threshold' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'low_balance_threshold' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'hospital name',
            'section' => 'section name',
            'system_name' => 'system name',
            'session_lifetime_minutes' => 'session lifetime',
            'large_deposit_threshold' => 'large deposit threshold',
            'low_balance_threshold' => 'low balance threshold',
        ];
    }
}
