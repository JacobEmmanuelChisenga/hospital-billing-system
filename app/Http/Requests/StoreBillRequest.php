<?php

namespace App\Http\Requests;

use App\Enums\PatientStatus;
use App\Enums\VisitType;
use App\Models\Patient;
use App\Services\BillService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessAccountsModules() ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'id')->where('status', PatientStatus::Active->value),
            ],
            'visit_date' => ['required', 'date', 'before_or_equal:today'],
            'visit_type' => ['required', Rule::enum(VisitType::class)],
            'ward_bed' => ['nullable', 'string', 'max:100'],
            'consultation_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'pharmacy_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'lab_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'ward_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'other_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        $total = BillService::calculateTotal($this->chargeInputs());

        if ($total <= 0) {
            // Custom validation in withValidator instead.
        } else {
            $patient = Patient::query()->with(['company', 'principalMember'])->find($this->input('patient_id'));
            $availableBalance = $patient ? (float) $patient->effectiveBalance() : 0;

            if ($total > $availableBalance) {
                $rules['confirm_insufficient_balance'] = ['accepted'];
            }
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $total = BillService::calculateTotal($this->chargeInputs());

            if ($total <= 0) {
                $validator->errors()->add('consultation_amount', 'Enter at least one charge amount greater than zero.');
            }

            $visitType = VisitType::tryFrom((string) $this->input('visit_type'));

            if ($visitType === VisitType::Ipd && ! $this->filled('ward_bed')) {
                $validator->errors()->add('ward_bed', 'Please enter the ward and bed for an inpatient visit.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'confirm_insufficient_balance.accepted' => 'Please confirm billing when the account balance is insufficient.',
            'visit_date.before_or_equal' => 'The visit date cannot be in the future.',
            'patient_id.exists' => 'Please select an active patient.',
        ];
    }

    public function attributes(): array
    {
        return [
            'patient_id' => 'patient',
            'visit_date' => 'visit date',
            'visit_type' => 'visit type',
            'ward_bed' => 'ward / bed',
            'confirm_insufficient_balance' => 'insufficient balance confirmation',
        ];
    }

    /** Normalised charge fields for total calculation. */
    public function chargeInputs(): array
    {
        return [
            'consultation_amount' => $this->input('consultation_amount', 0),
            'pharmacy_amount' => $this->input('pharmacy_amount', 0),
            'lab_amount' => $this->input('lab_amount', 0),
            'ward_amount' => $this->input('ward_amount', 0),
            'other_amount' => $this->input('other_amount', 0),
        ];
    }
}
