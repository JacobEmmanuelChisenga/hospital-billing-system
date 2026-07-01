<?php

namespace Tests\Feature;

use App\Enums\AuditActionType;
use App\Enums\PatientType;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_nursing_staff_can_access_patient_management(): void
    {
        $user = User::factory()->nursing()->create();
        Patient::factory()->member()->create(['name' => 'Reception Patient']);

        $this->actingAs($user)
            ->get(route('patients.index'))
            ->assertOk()
            ->assertSee('Reception Patient');
    }

    public function test_nursing_staff_can_register_a_patient(): void
    {
        $user = User::factory()->nursing()->create();

        $this->actingAs($user)->post(route('patients.store'), $this->patientPayload([
            'type' => PatientType::Member->value,
            'first_name' => 'Nurse',
            'last_name' => 'Registered',
            'phone_number' => '0977000000',
            'contact_address' => 'Plot 10, Lusaka',
            'next_of_kin_name' => 'Mary Registered',
            'next_of_kin_phone' => '0966000000',
            'next_of_kin_relationship' => 'Spouse',
        ]))->assertRedirect();

        $this->assertDatabaseHas('patients', [
            'name' => 'Nurse Registered',
            'type' => PatientType::Member->value,
            'phone_number' => '0977000000',
            'next_of_kin_name' => 'Mary Registered',
        ]);
        $this->assertDatabaseHas('memberships', [
            'status' => 'pending_payment',
        ]);
    }

    public function test_registry_search_can_find_patient_by_phone_number(): void
    {
        $user = User::factory()->registry()->create();
        Patient::factory()->member()->create([
            'name' => 'Phone Search Patient',
            'phone_number' => '0955123456',
        ]);

        $this->actingAs($user)
            ->get(route('patients.index', ['search' => '0955123456']))
            ->assertOk()
            ->assertSee('Phone Search Patient');
    }

    public function test_accounts_staff_can_view_patient_list(): void
    {
        $user = User::factory()->accounts()->create();
        Patient::factory()->member()->create(['name' => 'John Banda']);

        $this->actingAs($user)
            ->get(route('patients.index'))
            ->assertOk()
            ->assertSee('John Banda');
    }

    public function test_accounts_staff_cannot_register_a_member(): void
    {
        $user = User::factory()->accounts()->create();

        $this->actingAs($user)->post(route('patients.store'), $this->patientPayload([
            'type' => PatientType::Member->value,
            'first_name' => 'Mary',
            'last_name' => 'Phiri',
            'man_number' => 'MAN-2001',
        ]))->assertForbidden();

        $this->assertDatabaseMissing('patients', ['hc_number' => 'HC-1001']);
    }

    public function test_nursing_staff_can_register_a_dependant(): void
    {
        $user = User::factory()->nursing()->create();
        $principal = Patient::factory()->member()->create(['name' => 'Principal Member']);

        $response = $this->actingAs($user)->post(route('patients.store'), $this->patientPayload([
            'type' => PatientType::Dependant->value,
            'first_name' => 'Child',
            'last_name' => 'Dependant',
            'principal_patient_id' => $principal->id,
            'relationship' => 'Child',
        ]));

        $patient = Patient::query()->where('name', 'Child Dependant')->first();

        $response->assertRedirect(route('patients.show', $patient));
        $this->assertSame($principal->id, $patient->principal_patient_id);
        $this->assertSame('Child', $patient->relationship);
    }

    public function test_nursing_staff_can_register_company_patient_using_existing_company(): void
    {
        $user = User::factory()->nursing()->create();
        $company = Company::factory()->create(['name' => 'Zambia Mining Corp']);

        $response = $this->actingAs($user)->post(route('patients.store'), $this->patientPayload([
            'type' => PatientType::Company->value,
            'first_name' => 'Company',
            'last_name' => 'Employee',
            'company_id' => $company->id,
            'man_number' => 'MAN-2001',
            'department' => 'Operations',
        ]));

        $patient = Patient::query()->where('name', 'Company Employee')->first();

        $response->assertRedirect(route('patients.show', $patient));
        $this->assertSame($company->id, $patient->company_id);
        $this->assertSame('MAN-2001', $patient->man_number);
        $this->assertDatabaseMissing('memberships', ['patient_id' => $patient->id]);
    }

    public function test_nursing_staff_cannot_create_company_from_patient_form(): void
    {
        $user = User::factory()->nursing()->create();

        $this->actingAs($user)->post(route('patients.store'), $this->patientPayload([
            'type' => PatientType::Company->value,
            'first_name' => 'Company',
            'last_name' => 'Employee',
            'new_company_name' => 'Zambia Mining Corp',
        ]))->assertSessionHasErrors(['company_id', 'new_company_name']);

        $this->assertDatabaseMissing('companies', ['name' => 'Zambia Mining Corp']);
    }

    public function test_nursing_staff_can_update_patient_details(): void
    {
        $user = User::factory()->nursing()->create();
        $patient = Patient::factory()->member()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->put(route('patients.update', $patient), $this->patientPayload([
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'status' => 'active',
        ], includeType: false));

        $response->assertRedirect(route('patients.show', $patient));
        $this->assertSame('Updated Name', $patient->fresh()->name);
        $this->assertTrue(
            AuditLog::query()
                ->where('action_type', AuditActionType::PatientUpdated)
                ->where('related_id', $patient->id)
                ->exists()
        );
    }

    public function test_accounts_staff_cannot_update_patient_details(): void
    {
        $user = User::factory()->accounts()->create();
        $patient = Patient::factory()->member()->create(['name' => 'Old Name']);

        $this->actingAs($user)->put(route('patients.update', $patient), $this->patientPayload([
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'status' => 'active',
        ], includeType: false))->assertForbidden();

        $this->assertSame('Old Name', $patient->fresh()->name);
    }

    public function test_administrator_can_view_but_not_register_patients(): void
    {
        $user = User::factory()->administrator()->create();
        $patient = Patient::factory()->member()->create(['name' => 'Admin View']);

        $this->actingAs($user)
            ->get(route('patients.show', $patient))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('patients.create'))
            ->assertForbidden();
    }

    private function patientPayload(array $overrides = [], bool $includeType = true): array
    {
        $payload = [
            'first_name' => 'John',
            'last_name' => 'Banda',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'nrc_number' => fake()->unique()->bothify('######/##/#'),
            'nationality' => 'Zambian',
            'marital_status' => 'married',
            'phone_number' => '0977000000',
            'contact_address' => 'Plot 10, Lusaka',
            'town_city' => 'Lusaka',
            'next_of_kin_name' => 'Mary Banda',
            'next_of_kin_phone' => '0966000000',
            'next_of_kin_relationship' => 'Spouse',
        ];

        if ($includeType) {
            $payload['type'] = PatientType::Member->value;
        }

        return array_merge($payload, $overrides);
    }
}
