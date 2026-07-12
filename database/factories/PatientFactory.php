<?php

namespace Database\Factories;

use App\Enums\MembershipStatus;
use App\Enums\PatientStatus;
use App\Enums\PatientType;
use App\Models\Company;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Patient $patient): void {
            $updates = [];

            if (blank($patient->file_number)) {
                $prefix = config('hospital.file_number_prefix', 'RRGH');
                $updates['file_number'] = $prefix.'-'.str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT);
            }

            if (blank($patient->patient_number)) {
                $updates['patient_number'] = 'RR-'.str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT);
            }

            if ($updates !== []) {
                $patient->forceFill($updates)->saveQuietly();
            }
        });
    }

    public function definition(): array
    {
        return [
            'type' => PatientType::Member,
            'patient_number' => fake()->unique()->bothify('RR-######'),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional()->firstName(),
            'last_name' => fake()->lastName(),
            'name' => fake()->name(),
            'gender' => fake()->randomElement(['female', 'male']),
            'date_of_birth' => fake()->dateTimeBetween('-80 years', '-1 year')->format('Y-m-d'),
            'hc_number' => fake()->unique()->bothify('HC-####'),
            'man_number' => fake()->optional()->bothify('MAN-####'),
            'department' => null,
            'employment_status' => null,
            'company_id' => null,
            'principal_patient_id' => null,
            'relationship' => null,
            'file_number' => null,
            'nrc_number' => fake()->optional()->bothify('######/##/#'),
            'nationality' => 'Zambian',
            'marital_status' => fake()->randomElement(['single', 'married', 'widowed', 'divorced']),
            'phone_number' => fake()->optional()->phoneNumber(),
            'alternative_phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'contact_address' => fake()->optional()->address(),
            'town_city' => fake()->city(),
            'next_of_kin_name' => fake()->optional()->name(),
            'next_of_kin_phone' => fake()->optional()->phoneNumber(),
            'next_of_kin_relationship' => fake()->optional()->randomElement(['Spouse', 'Child', 'Parent', 'Sibling']),
            'balance' => 0,
            'status' => PatientStatus::Active,
            'membership_status' => MembershipStatus::NotApplicable,
            'notes' => null,
        ];
    }

    public function member(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PatientType::Member,
            'company_id' => null,
            'principal_patient_id' => null,
            'relationship' => null,
            'man_number' => null,
            'department' => null,
            'employment_status' => null,
            'membership_status' => MembershipStatus::PendingPayment,
        ]);
    }

    public function dependant(?Patient $principal = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PatientType::Dependant,
            'company_id' => null,
            'principal_patient_id' => $principal?->id ?? Patient::factory()->member(),
            'relationship' => fake()->randomElement(['Spouse', 'Child', 'Parent']),
            'balance' => 0,
            'man_number' => null,
            'department' => null,
            'employment_status' => null,
            'membership_status' => MembershipStatus::PendingPayment,
        ]);
    }

    public function companyPatient(?Company $company = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PatientType::Company,
            'company_id' => $company?->id ?? Company::factory(),
            'principal_patient_id' => null,
            'relationship' => null,
            'balance' => 0,
            'man_number' => fake()->unique()->bothify('MAN-####'),
            'department' => fake()->optional()->word(),
            'employment_status' => fake()->optional()->randomElement(['Active', 'Retired', 'Contract']),
        ]);
    }

    public function cashPatient(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PatientType::CashPatient,
            'company_id' => null,
            'principal_patient_id' => null,
            'relationship' => null,
            'balance' => 0,
            'hc_number' => null,
            'man_number' => null,
            'department' => null,
            'employment_status' => null,
            'membership_status' => MembershipStatus::NotApplicable,
        ]);
    }
}
