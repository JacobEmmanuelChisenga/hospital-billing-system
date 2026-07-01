<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageVisits() ?? false;
    }

    public function rules(): array
    {
        return [
            'void_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'void_reason.required' => 'Please enter a reason for voiding this bill.',
            'void_reason.min' => 'The reason must be at least :min characters so the void is properly documented.',
        ];
    }

    public function attributes(): array
    {
        return [
            'void_reason' => 'reason for voiding',
        ];
    }
}
