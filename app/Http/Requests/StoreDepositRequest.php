<?php

namespace App\Http\Requests;

use App\Enums\PatientType;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepositRequest extends FormRequest
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
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'id')->where('type', PatientType::Member->value),
            ],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'deposit_date' => ['required', 'date', 'before_or_equal:today'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        // Large deposits need an explicit confirmation tick from staff.
        if ($amount >= $threshold) {
            $rules['confirm_large_deposit'] = ['accepted'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'confirm_large_deposit.accepted' => 'Please confirm this large deposit before saving.',
            'patient_id.exists' => 'Please select a valid member account.',
            'payment_method.required' => 'Please select how the money was received.',
            'deposit_date.before_or_equal' => 'The deposit date cannot be in the future.',
        ];
    }

    public function attributes(): array
    {
        return [
            'patient_id' => 'member',
            'payment_method' => 'payment method',
            'deposit_date' => 'deposit date',
            'confirm_large_deposit' => 'large deposit confirmation',
        ];
    }
}
