<?php

namespace Tests\Feature;

use App\Enums\AuditActionType;
use App\Enums\BillStatus;
use App\Enums\MembershipStatus;
use App\Enums\VisitStatus;
use App\Enums\VisitType;
use App\Models\BillableService;
use App\Models\Bill;
use App\Models\Company;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\BillableServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BillableServiceSeeder::class);
    }

    public function test_consultant_cannot_access_receipts(): void
    {
        $user = User::factory()->consultant()->create();

        $this->actingAs($user)
            ->get(route('billing.index'))
            ->assertForbidden();
    }

    public function test_accounts_cannot_post_bills_directly(): void
    {
        $user = User::factory()->accounts()->create();
        $member = Patient::factory()->member()->create(['balance' => 5000]);

        $this->actingAs($user)->post(route('visits.store'), [
            'patient_id' => $member->id,
            'visit_date' => now()->toDateString(),
            'visit_type' => VisitType::Opd->value,
        ])->assertForbidden();
    }

    public function test_registry_clerk_can_post_bill_via_visit_and_deduct_balance(): void
    {
        $user = User::factory()->registry()->create();
        $member = Patient::factory()->member()->create([
            'balance' => 5000,
            'membership_status' => MembershipStatus::Active,
            'membership_valid_until' => now()->addYear(),
        ]);

        $visit = $this->openVisit($user, $member);
        $this->recordClinicalNotes($visit);
        $this->addServiceCharge($user, $visit, 'Consultation');
        $this->addServiceCharge($user, $visit, 'Pharmacy');

        $response = $this->actingAs($user)->post(route('visits.post-bill', $visit));

        $bill = Bill::query()->first();
        $visit = $visit->fresh();

        $response->assertRedirect(route('billing.receipt', $bill));
        $this->assertSame('4730.00', $member->fresh()->balance);
        $this->assertSame('270.00', $bill->total_amount);
        $this->assertSame($member->id, $bill->account_patient_id);
        $this->assertSame(VisitStatus::Completed, $visit->status);
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => AuditActionType::BillCreated->value,
            'related_id' => $bill->id,
        ]);
    }

    public function test_dependant_visit_bill_deducts_principal_member_balance(): void
    {
        $user = User::factory()->registry()->create();
        $principal = Patient::factory()->member()->create([
            'balance' => 3000,
            'membership_status' => MembershipStatus::Active,
            'membership_valid_until' => now()->addYear(),
        ]);
        $dependant = Patient::factory()->dependant($principal)->create([
            'membership_status' => MembershipStatus::Active,
            'membership_valid_until' => now()->addYear(),
        ]);

        $visit = $this->openVisit($user, $dependant, VisitType::Emergency);
        $this->recordClinicalNotes($visit);
        $this->addServiceCharge($user, $visit, 'Consultation');

        $this->actingAs($user)->post(route('visits.post-bill', $visit));

        $this->assertSame('2850.00', $principal->fresh()->balance);
        $this->assertSame($principal->id, Bill::query()->first()->account_patient_id);
    }

    public function test_dependant_visit_is_ready_when_principal_membership_is_active_and_funded(): void
    {
        $user = User::factory()->registry()->create();
        $principal = Patient::factory()->member()->create([
            'balance' => 48550,
            'membership_status' => MembershipStatus::Active,
            'membership_valid_until' => now()->addYear(),
        ]);
        // Dependant's own membership fee may still be pending — coverage is via principal.
        $dependant = Patient::factory()->dependant($principal)->create([
            'membership_status' => MembershipStatus::PendingPayment,
            'membership_valid_until' => null,
        ]);

        $visit = $this->openVisit($user, $dependant, VisitType::Ipd, 'Ward 2 / Bed 4');

        $this->assertSame(VisitStatus::ReadyForConsultation, $visit->status);
    }

    public function test_stuck_dependant_visit_is_released_when_principal_already_covers_care(): void
    {
        $user = User::factory()->registry()->create();
        $principal = Patient::factory()->member()->create([
            'balance' => 48550,
            'membership_status' => MembershipStatus::Active,
            'membership_valid_until' => now()->addYear(),
        ]);
        $dependant = Patient::factory()->dependant($principal)->create([
            'membership_status' => MembershipStatus::PendingPayment,
        ]);

        $visit = Visit::query()->create([
            'patient_id' => $dependant->id,
            'visit_date' => now()->toDateString(),
            'visit_type' => VisitType::Ipd,
            'status' => VisitStatus::AwaitingPayment,
            'opened_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('visits.show', $visit))
            ->assertOk()
            ->assertSee('Waiting for Consultant');

        $this->assertSame(VisitStatus::ReadyForConsultation, $visit->fresh()->status);
    }

    public function test_dependant_visit_awaits_payment_when_principal_membership_is_pending(): void
    {
        $user = User::factory()->registry()->create();
        $principal = Patient::factory()->member()->create([
            'balance' => 5000,
            'membership_status' => MembershipStatus::PendingPayment,
            'membership_valid_until' => null,
        ]);
        $dependant = Patient::factory()->dependant($principal)->create();

        $visit = $this->openVisit($user, $dependant);

        $this->assertSame(VisitStatus::AwaitingPayment, $visit->status);
    }

    public function test_company_patient_visit_bill_deducts_company_pool(): void
    {
        $user = User::factory()->registry()->create();
        $company = Company::factory()->create(['balance' => 20000]);
        $patient = Patient::factory()->companyPatient($company)->create();

        $visit = $this->openVisit($user, $patient, VisitType::Ipd, 'Ward 3 / Bed 12');
        $this->recordClinicalNotes($visit);
        $this->addServiceCharge($user, $visit, 'Ward');
        $this->addServiceCharge($user, $visit, 'Consultation');

        $this->actingAs($user)->post(route('visits.post-bill', $visit));

        $bill = Bill::query()->first();

        $this->assertSame('19350.00', $company->fresh()->balance);
        $this->assertSame($company->id, $bill->company_id);
        $this->assertNull($bill->account_patient_id);
    }

    public function test_insufficient_balance_requires_confirmation_on_visit_bill(): void
    {
        $user = User::factory()->registry()->create();
        $member = Patient::factory()->member()->create([
            'balance' => 100,
            'membership_status' => MembershipStatus::Active,
            'membership_valid_until' => now()->addYear(),
        ]);
        $visit = $this->openVisit($user, $member);
        $this->recordClinicalNotes($visit);
        $this->addServiceCharge($user, $visit, 'Consultation');

        $this->actingAs($user)->post(route('visits.post-bill', $visit))
            ->assertSessionHasErrors('confirm_insufficient_balance');

        $this->assertSame('100.00', $member->fresh()->balance);
        $this->assertDatabaseCount('bills', 0);
    }

    public function test_registry_clerk_can_void_bill_and_restore_balance(): void
    {
        $user = User::factory()->registry()->create();
        $member = Patient::factory()->member()->create(['balance' => 4000]);
        $bill = Bill::factory()->create([
            'patient_id' => $member->id,
            'account_patient_id' => $member->id,
            'total_amount' => 600,
            'consultation_amount' => 600,
            'created_by' => $user->id,
        ]);
        $member->update(['balance' => 3400]);

        $response = $this->actingAs($user)->post(route('billing.void', $bill), [
            'void_reason' => 'Bill entered for wrong patient by mistake.',
        ]);

        $response->assertRedirect(route('billing.show', $bill));
        $this->assertSame(BillStatus::Voided, $bill->fresh()->status);
        $this->assertSame('4000.00', $member->fresh()->balance);
    }

    public function test_receipt_page_is_accessible_to_accounts(): void
    {
        $user = User::factory()->accounts()->create();
        $bill = Bill::factory()->create(['created_by' => User::factory()->registry()->create()->id]);

        $this->actingAs($user)
            ->get(route('billing.receipt', $bill))
            ->assertOk()
            ->assertSee('OFFICIAL RECEIPT');
    }

    public function test_member_receipt_shows_remaining_balance(): void
    {
        $user = User::factory()->registry()->create();
        $member = Patient::factory()->member()->create([
            'balance' => 5000,
            'membership_status' => MembershipStatus::Active,
            'membership_valid_until' => now()->addYear(),
        ]);

        $visit = $this->openVisit($user, $member);
        $this->recordClinicalNotes($visit);
        $this->addServiceCharge($user, $visit, 'Consultation');
        $this->actingAs($user)->post(route('visits.post-bill', $visit));

        $bill = Bill::query()->firstOrFail();

        $this->actingAs($user)
            ->get(route('billing.receipt', $bill))
            ->assertOk()
            ->assertSee('Remaining Balance')
            ->assertSee('K '.number_format((float) $member->fresh()->balance, 2));
    }

    public function test_company_patient_receipt_hides_company_balance(): void
    {
        $user = User::factory()->registry()->create();
        $company = Company::factory()->create(['balance' => 20000, 'name' => 'Acme Mining']);
        $patient = Patient::factory()->companyPatient($company)->create();

        $visit = $this->openVisit($user, $patient, VisitType::Ipd, 'Ward 1 / Bed 2');
        $this->recordClinicalNotes($visit);
        $this->addServiceCharge($user, $visit, 'Consultation');
        $this->actingAs($user)->post(route('visits.post-bill', $visit));

        $bill = Bill::query()->firstOrFail();

        $this->actingAs($user)
            ->get(route('billing.receipt', $bill))
            ->assertOk()
            ->assertSee('OFFICIAL RECEIPT')
            ->assertSee('Acme Mining')
            ->assertDontSee('Remaining Balance')
            ->assertDontSee('K 19,850.00');
    }

    public function test_consultant_can_record_clinical_notes_on_open_visit(): void
    {
        $registry = User::factory()->registry()->create();
        $consultant = User::factory()->consultant()->create();
        $member = Patient::factory()->member()->create([
            'balance' => 500,
            'membership_status' => MembershipStatus::Active,
            'membership_valid_until' => now()->addYear(),
        ]);
        $visit = $this->openVisit($registry, $member);

        $this->actingAs($consultant)->post(route('clinical-notes.store', $visit), [
            'complaint' => 'Headache for 2 days',
            'vitals' => 'BP 120/80, Temp 37.1',
            'examination_findings' => 'Mild dehydration',
            'diagnosis' => 'Tension headache',
            'treatment_notes' => 'Paracetamol 1g',
            'procedures_performed' => 'Injection administered',
            'follow_up_instructions' => 'Return if symptoms persist',
        ])->assertRedirect(route('consultant.queue'));

        $this->assertDatabaseHas('clinical_notes', [
            'visit_id' => $visit->id,
            'complaint' => 'Headache for 2 days',
            'examination_findings' => 'Mild dehydration',
            'procedures_performed' => 'Injection administered',
        ]);
        $this->assertSame(VisitStatus::AwaitingBilling, $visit->fresh()->status);
    }

    public function test_member_registration_sets_pending_membership_status(): void
    {
        $user = User::factory()->registry()->create();

        $this->actingAs($user)->post(route('patients.store'), [
            'type' => 'member',
            'first_name' => 'John',
            'last_name' => 'Banda',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'nrc_number' => '900101/10/1',
            'nationality' => 'Zambian',
            'marital_status' => 'married',
            'phone_number' => '0977000000',
            'contact_address' => 'Plot 10, Lusaka',
            'town_city' => 'Lusaka',
            'next_of_kin_name' => 'Mary Banda',
            'next_of_kin_phone' => '0966000000',
            'next_of_kin_relationship' => 'Spouse',
        ])->assertRedirect();

        $patient = Patient::query()->where('name', 'John Banda')->firstOrFail();

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'man_number' => null,
            'membership_status' => 'pending_payment',
        ]);
        $this->assertDatabaseHas('memberships', [
            'patient_id' => $patient->id,
            'status' => 'pending_payment',
        ]);
    }

    private function openVisit(User $user, Patient $patient, VisitType $type = VisitType::Opd, ?string $wardBed = null): Visit
    {
        $payload = [
            'patient_id' => $patient->id,
            'visit_date' => now()->toDateString(),
            'visit_type' => $type->value,
        ];

        if ($wardBed !== null) {
            $payload['ward_bed'] = $wardBed;
        }

        $this->actingAs($user)->post(route('visits.store'), $payload)->assertRedirect();

        return Visit::query()->where('patient_id', $patient->id)->latest('id')->firstOrFail();
    }

    private function recordClinicalNotes(Visit $visit): void
    {
        $consultant = User::factory()->consultant()->create();

        $this->actingAs($consultant)->post(route('clinical-notes.store', $visit), [
            'complaint' => 'Severe headache',
            'vitals' => 'Temp 38.1',
            'examination_findings' => 'Fever and chills',
            'diagnosis' => 'Malaria',
            'treatment_notes' => 'Medication administered',
            'procedures_performed' => 'Malaria test',
        ])->assertRedirect(route('consultant.queue'));
    }

    private function addServiceCharge(User $user, Visit $visit, string $serviceName): void
    {
        $service = BillableService::query()->where('name', $serviceName)->firstOrFail();

        $this->actingAs($user)->post(route('visits.charges.store', $visit), [
            'billable_service_id' => $service->id,
        ])->assertRedirect();
    }
}
