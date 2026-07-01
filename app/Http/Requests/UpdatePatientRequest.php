<?php

namespace App\Http\Requests;

use App\Enums\PatientStatus;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends PatientRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['status'] = ['required', Rule::enum(PatientStatus::class)];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('status')) {
            $this->merge(['status' => PatientStatus::Active->value]);
        }
    }
}
