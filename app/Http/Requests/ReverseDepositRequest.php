<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReverseDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canPerformFinancialOperations() ?? false;
    }

    public function rules(): array
    {
        return [
            'reversal_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reversal_reason.required' => 'Please enter a reason for reversing this deposit.',
            'reversal_reason.min' => 'The reason must be at least :min characters so the reversal is properly documented.',
        ];
    }

    public function attributes(): array
    {
        return [
            'reversal_reason' => 'reason for reversal',
        ];
    }
}
