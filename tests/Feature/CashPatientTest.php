<?php

namespace Tests\Feature;

use App\Enums\MembershipStatus;
use App\Enums\PatientType;
use App\Enums\PaymentMethod;
use App\Enums\VisitStatus;
use App\Enums\VisitType;
use App\Models\Bill;
use App\Models\BillableService;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\BillableServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashPatientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BillableServiceSeeder::class);
    }

    public function test_registry_can_register_casual_caller_without_membership(): void
    {
        $user = User::factory()->registry()->create();

        $this->actingAs($user)->post(route('patients.store'), [
            'type' => PatientType::CashPatient->value,
            'first_name' => 'Mary',
            'last_name' => 'Phiri',
            'gender' => 'female',
            'date_of_birth' => '1985-05-10',
            'nrc_number' => '850510/10/1',
            'nationality' => 'Zambian',
            'marital_status' => 'married',
            'phone_number' => '0977111222',
            'contact_address' => 'Plot 5, Lusaka',
            'town_city' => 'Lusaka',
            'next_of_kin_name' => 'John Phiri',
            'next_of_kin_phone' => '0966000111',
            'next_of_kin_relationship' => 'Spouse',
        ])->assertRedirect();

        $patient = Patient::query()->where('name', 'Mary Phiri')->firstOrFail();

        $this->assertSame(PatientType::CashPatient, $patient->type);
        $this->assertSame(MembershipStatus::NotApplicable, $patient->membership_status);
        $this->assertNull($patient->hc_number);
        $this->assertSame(
            'RRGH-'.str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT),
            $patient->file_number,
        );
        $this->assertDatabaseMissing('memberships', ['patient_id' => $patient->id]);
    }

    public function test_casual_caller_visit_bill_awaits_accounts_payment(): void
    {
        $registry = User::factory()->registry()->create();
        $patient = Patient::factory()->cashPatient()->create();

        $visit = $this->openVisit($registry, $patient);
        $this->recordClinicalNotes($visit);
        $this->addServiceCharge($registry, $visit, 'Consultation');
        $this->addServiceCharge($registry, $visit, 'Pharmacy');

        $response = $this->actingAs($registry)->post(route('visits.post-bill', $visit));

        $bill = Bill::query()->firstOrFail();
        $visit = $visit->fresh();

        $response->assertRedirect(route('billing.show', $bill));
        $this->assertSame('0.00', $patient->fresh()->balance);
        $this->assertNull($bill->account_patient_id);
        $this->assertNull($bill->company_id);
        $this->assertNull($bill->paid_at);
        $this->assertSame(VisitStatus::Billed, $visit->status);
        $this->assertTrue($bill->isOutstanding());
    }

    public function test_accounts_can_collect_cash_payment_and_complete_visit(): void
    {
        $registry = User::factory()->registry()->create();
        $accounts = User::factory()->accounts()->create();
        $patient = Patient::factory()->cashPatient()->create();

        $visit = $this->openVisit($registry, $patient);
        $this->recordClinicalNotes($visit);
        $this->addServiceCharge($registry, $visit, 'Consultation');

        $this->actingAs($registry)->post(route('visits.post-bill', $visit));
        $bill = Bill::query()->firstOrFail();

        $response = $this->actingAs($accounts)->post(route('billing.collect-payment', $bill), [
            'payment_method' => PaymentMethod::Cash->value,
        ]);

        $bill = $bill->fresh();
        $visit = $visit->fresh();

        $response->assertRedirect(route('billing.receipt', $bill));
        $this->assertSame(PaymentMethod::Cash, $bill->payment_method);
        $this->assertNotNull($bill->paid_at);
        $this->assertSame($accounts->id, $bill->paid_by);
        $this->assertTrue($bill->isPaid());
        $this->assertSame(VisitStatus::Completed, $visit->status);
    }

    public function test_casual_caller_statement_redirects_to_profile(): void
    {
        $accounts = User::factory()->accounts()->create();
        $patient = Patient::factory()->cashPatient()->create();

        $this->actingAs($accounts)
            ->get(route('reports.patient-statement', $patient))
            ->assertRedirect(route('patients.show', $patient));
    }

    private function openVisit(User $user, Patient $patient, VisitType $type = VisitType::Opd): Visit
    {
        $this->actingAs($user)->post(route('visits.store'), [
            'patient_id' => $patient->id,
            'visit_date' => now()->toDateString(),
            'visit_type' => $type->value,
        ])->assertRedirect();

        return Visit::query()->where('patient_id', $patient->id)->latest('id')->firstOrFail();
    }

    private function recordClinicalNotes(Visit $visit): void
    {
        $nurse = User::factory()->nurse()->create();

        $this->actingAs($nurse)->post(route('clinical-notes.store', $visit), [
            'complaint' => 'General consultation',
            'vitals' => 'BP 120/80',
            'examination_findings' => 'Stable',
            'diagnosis' => 'Minor ailment',
            'treatment_notes' => 'Treatment given',
            'procedures_performed' => 'Dressing',
        ])->assertRedirect(route('nurse.queue'));
    }

    private function addServiceCharge(User $user, Visit $visit, string $serviceName): void
    {
        $service = BillableService::query()->where('name', $serviceName)->firstOrFail();

        $this->actingAs($user)->post(route('visits.charges.store', $visit), [
            'billable_service_id' => $service->id,
        ])->assertRedirect();
    }
}
