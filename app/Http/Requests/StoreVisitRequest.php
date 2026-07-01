<?php

namespace App\Http\Requests;

use App\Enums\VisitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageVisits() ?? false;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'visit_date' => ['required', 'date', 'before_or_equal:today'],
            'visit_type' => ['required', Rule::enum(VisitType::class)],
            'ward_bed' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $visitType = VisitType::tryFrom((string) $this->input('visit_type'));

            if ($visitType === VisitType::Ipd && ! $this->filled('ward_bed')) {
                $validator->errors()->add('ward_bed', 'Please enter the ward and bed for an inpatient visit.');
            }
        });
    }
}
