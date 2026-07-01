<?php

namespace App\Http\Requests;

use App\Enums\PatientType;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMembershipFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canPerformFinancialOperations() ?? false;
    }

    public function rules(): array
    {
        return [
            // The membership holder may be a member joining the scheme or a dependant.
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'id')->whereIn('type', [
                    PatientType::Member->value,
                    PatientType::Dependant->value,
                ]),
            ],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:100'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'expiry_date' => ['required', 'date', 'after:payment_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Please select the member or dependant the membership is for.',
            'patient_id.exists' => 'Please select a valid member or dependant.',
            'payment_method.required' => 'Please select how the membership fee was received.',
            'payment_date.before_or_equal' => 'The payment date cannot be in the future.',
            'expiry_date.after' => 'The expiry date must be after the payment date.',
        ];
    }

    public function attributes(): array
    {
        return [
            'patient_id' => 'member or dependant',
            'payment_method' => 'payment method',
            'payment_date' => 'payment date',
            'expiry_date' => 'expiry date',
        ];
    }
}
