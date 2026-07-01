<?php

namespace Tests\Feature;

use App\Enums\AuditActionType;
use App\Enums\PatientType;
use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\Company;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreBillingModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_bills_from_own_account(): void
    {
        $member = Patient::factory()->member()->create(['balance' => 500]);

        $this->assertTrue($member->isMember());
        $this->assertTrue($member->billableAccountPatient()->is($member));
        $this->assertNull($member->billableCompany());
    }

    public function test_dependant_bills_from_principal_member_account(): void
    {
        $principal = Patient::factory()->member()->create(['balance' => 1000]);
        $dependant = Patient::factory()->dependant($principal)->create();

        $this->assertTrue($dependant->isDependant());
        $this->assertTrue($dependant->billableAccountPatient()->is($principal));
        $this->assertTrue($principal->dependants()->first()->is($dependant));
    }

    public function test_company_patient_bills_from_company_pool(): void
    {
        $company = Company::factory()->create(['balance' => 25000]);
        $patient = Patient::factory()->companyPatient($company)->create();

        $this->assertTrue($patient->isCompanyPatient());
        $this->assertNull($patient->billableAccountPatient());
        $this->assertTrue($patient->billableCompany()->is($company));
        $this->assertSame(PatientType::Company, $patient->type);
    }

    public function test_bill_knows_the_patient_and_payer(): void
    {
        $principal = Patient::factory()->member()->create();
        $dependant = Patient::factory()->dependant($principal)->create();

        $bill = Bill::factory()->create([
            'patient_id' => $dependant->id,
            'account_patient_id' => $principal->id,
            'company_id' => null,
            'total_amount' => 150,
        ]);

        $this->assertTrue($bill->patient->is($dependant));
        $this->assertTrue($bill->accountPatient->is($principal));
        $this->assertSame($principal->name, $bill->payerName());
    }

    public function test_audit_log_can_reference_related_record(): void
    {
        $patient = Patient::factory()->member()->create();

        $auditLog = AuditLog::factory()->create([
            'action_type' => AuditActionType::PatientCreated,
            'description' => 'Registered a new member account.',
            'related_type' => Patient::class,
            'related_id' => $patient->id,
        ]);

        $this->assertTrue($auditLog->related->is($patient));
        $this->assertSame(AuditActionType::PatientCreated, $auditLog->action_type);
    }
}
