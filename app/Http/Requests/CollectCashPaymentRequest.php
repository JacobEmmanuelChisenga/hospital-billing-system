<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollectCashPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canPerformFinancialOperations() ?? false;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Please select how the patient paid.',
        ];
    }
}
