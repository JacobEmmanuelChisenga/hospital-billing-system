<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostVisitBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageVisits() ?? false;
    }

    public function rules(): array
    {
        $visit = $this->route('visit');
        $total = $visit ? $visit->chargesTotal() : 0;
        $patient = $visit?->patient;
        $available = $patient ? (float) $patient->effectiveBalance() : 0;

        $rules = [];

        if ($total > $available) {
            $rules['confirm_insufficient_balance'] = ['accepted'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'confirm_insufficient_balance.accepted' => 'Please confirm billing with insufficient balance before posting.',
        ];
    }
}
