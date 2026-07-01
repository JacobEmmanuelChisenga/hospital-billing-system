<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates date range filters shared across all report screens.
 */
class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canViewFinancialRecords() ?? false;
    }

    public function rules(): array
    {
        return [
            'preset' => ['nullable', 'in:today,week,month,custom'],
            'from_date' => ['nullable', 'date', 'required_if:preset,custom'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date', 'required_if:preset,custom'],
            'visit_type' => ['nullable', 'in:OPD,IPD,Emergency'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'from_date' => 'from date',
            'to_date' => 'to date',
            'visit_type' => 'visit type',
        ];
    }
}
