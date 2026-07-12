<?php

namespace App\Http\Requests;

use App\Enums\PatientStatus;
use App\Enums\PatientType;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Shared validation for creating and updating patients.
 *
 * Rules change depending on patient type — members stand alone, dependants
 * need a principal member, and company patients need a company link.
 */
abstract class PatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManagePatientDemographics() ?? false;
    }

    public function rules(): array
    {
        $patientId = $this->route('patient')?->id;

        return [
            'type' => $this->isMethod('post')
                ? ['required', Rule::enum(PatientType::class)]
                : ['prohibited'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'nationality' => ['required', 'string', 'max:100'],
            'marital_status' => ['required', 'string', 'max:50'],
            'nrc_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('patients', 'nrc_number')->ignore($patientId),
            ],
            'phone_number' => ['required', 'string', 'max:50'],
            'alternative_phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_address' => ['required', 'string', 'max:255'],
            'town_city' => ['required', 'string', 'max:100'],
            'next_of_kin_name' => ['required', 'string', 'max:255'],
            'next_of_kin_phone' => ['required', 'string', 'max:50'],
            'next_of_kin_relationship' => ['required', 'string', 'max:100'],
            'hc_number' => [
                'prohibited',
            ],
            'file_number' => [
                'prohibited',
            ],
            'man_number' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'employment_status' => ['nullable', 'string', 'max:100'],
            'relationship' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', Rule::enum(PatientStatus::class)],

            // Dependant fields — required only when type is dependant (checked in withValidator).
            'principal_patient_id' => ['nullable', 'integer', 'exists:patients,id'],

            // Company fields — Accounts creates company accounts separately.
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'new_company_name' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->patientType();

            if ($type === PatientType::Dependant) {
                if (! $this->input('principal_patient_id')) {
                    $validator->errors()->add('principal_patient_id', 'Please select the principal member for this dependant.');
                } else {
                    $principal = Patient::query()->with('membership')->find($this->input('principal_patient_id'));

                    if (! $principal?->isMember()) {
                        $validator->errors()->add('principal_patient_id', 'The principal must be an active member account.');
                    } elseif (blank($principal->effectiveMembershipNumber())) {
                        $validator->errors()->add('principal_patient_id', 'The principal member must have a membership number before registering a dependant.');
                    }
                }

                if (! $this->input('relationship')) {
                    $validator->errors()->add('relationship', 'Please enter the relationship to the principal member.');
                }
            }

            if ($type === PatientType::Company) {
                if (! $this->input('company_id')) {
                    $validator->errors()->add('company_id', 'Select an existing company account. Accounts must create missing companies first.');
                }

                if (! $this->input('man_number')) {
                    $validator->errors()->add('man_number', 'Enter the employee MAN number for company patients.');
                }
            }

            if ($type === PatientType::CashPatient) {
                foreach (['principal_patient_id', 'relationship', 'company_id', 'man_number', 'department', 'employment_status'] as $field) {
                    if ($this->filled($field)) {
                        $validator->errors()->add($field, 'Casual callers are not linked to members or companies.');
                    }
                }
            }

            if ($type !== PatientType::Company) {
                foreach (['man_number', 'department', 'employment_status'] as $companyField) {
                    if ($this->filled($companyField)) {
                        $validator->errors()->add($companyField, 'Company employment fields are only used for company patients.');
                    }
                }
            }

            if ($type === PatientType::Member && $this->filled('principal_patient_id')) {
                $validator->errors()->add('principal_patient_id', 'Members cannot be linked to a principal member.');
            }
        });
    }

    protected function patientType(): ?PatientType
    {
        if ($this->isMethod('post')) {
            return PatientType::tryFrom((string) $this->input('type'));
        }

        /** @var Patient|null $patient */
        $patient = $this->route('patient');

        return $patient?->type;
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Please enter the patient first name.',
            'last_name.required' => 'Please enter the patient last name.',
            'type.required' => 'Please select a patient type.',
            'status.required' => 'Please select a patient status.',
        ];
    }

    public function attributes(): array
    {
        return [
            'hc_number' => 'HC number',
            'man_number' => 'MAN number',
            'date_of_birth' => 'date of birth',
            'file_number' => 'file number',
            'nrc_number' => 'NRC number',
            'phone_number' => 'phone number',
            'next_of_kin_name' => 'next of kin name',
            'next_of_kin_phone' => 'next of kin phone',
            'next_of_kin_relationship' => 'next of kin relationship',
            'principal_patient_id' => 'principal member',
            'new_company_name' => 'company name',
        ];
    }
}
