<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canPerformFinancialOperations() ?? false;
    }

    public function rules(): array
    {
        $threshold = config('hospital.large_deposit_threshold');
        $amount = (float) $this->input('amount', 0);

        $rules = [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'deposit_date' => ['required', 'date', 'before_or_equal:today'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        if ($amount >= $threshold) {
            $rules['confirm_large_deposit'] = ['accepted'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'confirm_large_deposit.accepted' => 'Please confirm this large deposit before saving.',
            'deposit_date.before_or_equal' => 'The deposit date cannot be in the future.',
        ];
    }

    public function attributes(): array
    {
        return [
            'deposit_date' => 'deposit date',
            'confirm_large_deposit' => 'large deposit confirmation',
        ];
    }
}
