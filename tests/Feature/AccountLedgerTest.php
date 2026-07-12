<?php

namespace Tests\Feature;

use App\Enums\LedgerAccountType;
use App\Enums\LedgerTransactionType;
use App\Enums\MembershipStatus;
use App\Enums\PaymentMethod;
use App\Enums\VisitType;
use App\Models\AccountLedger;
use App\Models\BillableService;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Services\LedgerService;
use Database\Seeders\BillableServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BillableServiceSeeder::class);
    }

    public function test_deposit_and_bill_write_running_ledger_balance(): void
    {
        $accounts = User::factory()->accounts()->create();
        $registry = User::factory()->registry()->create();
        $member = Patient::factory()->member()->create([
            'balance' => 0,
            'membership_status' => MembershipStatus::Active,
            'membership_valid_until' => now()->addYear(),
        ]);

        $this->actingAs($accounts)->post(route('deposits.store'), [
            'patient_id' => $member->id,
            'amount' => 2000,
            'payment_method' => PaymentMethod::Cash->value,
            'deposit_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('account_ledgers', [
            'account_type' => LedgerAccountType::Member->value,
            'account_id' => $member->id,
            'transaction_type' => LedgerTransactionType::Deposit->value,
            'credit' => '2000.00',
            'running_balance' => '2000.00',
        ]);

        $visit = $this->openVisit($registry, $member);
        $this->recordClinicalNotes($visit);
        $service = BillableService::query()->where('name', 'Consultation')->firstOrFail();
        $this->actingAs($registry)->post(route('visits.charges.store', $visit), [
            'billable_service_id' => $service->id,
        ]);
        $this->actingAs($registry)->post(route('visits.post-bill', $visit));

        $consultationPrice = (float) $service->price;

        $this->assertTrue(
            AccountLedger::query()
                ->where('account_id', $member->id)
                ->where('transaction_type', LedgerTransactionType::Bill)
                ->where('debit', $consultationPrice)
                ->where('running_balance', 2000 - $consultationPrice)
                ->exists()
        );
    }

    public function test_member_statement_is_bank_style_ledger(): void
    {
        $accounts = User::factory()->accounts()->create();
        $member = Patient::factory()->member()->create([
            'name' => 'John Banda',
            'balance' => 0,
            'hc_number' => 'HC-000154',
            'membership_status' => MembershipStatus::Active,
            'membership_valid_until' => now()->addYear(),
        ]);

        $this->actingAs($accounts)->post(route('deposits.store'), [
            'patient_id' => $member->id,
            'amount' => 4500,
            'payment_method' => PaymentMethod::Cash->value,
            'deposit_date' => now()->toDateString(),
        ]);

        $statement = app(LedgerService::class)->memberStatement($member, today()->startOfDay(), today()->endOfDay());

        $this->assertSame(0.0, $statement['opening_balance']);
        $this->assertSame(4500.0, $statement['deposits_total']);
        $this->assertSame(4500.0, $statement['closing_balance']);
        $this->assertSame('OPENING', $statement['lines']->first()['reference']);
        $this->assertStringContainsString('Deposit', $statement['lines']->last()['description']);
    }

    private function openVisit(User $user, Patient $patient): Visit
    {
        $this->actingAs($user)->post(route('visits.store'), [
            'patient_id' => $patient->id,
            'visit_date' => now()->toDateString(),
            'visit_type' => VisitType::Opd->value,
        ])->assertRedirect();

        return Visit::query()->where('patient_id', $patient->id)->latest('id')->firstOrFail();
    }

    private function recordClinicalNotes(Visit $visit): void
    {
        $consultant = User::factory()->consultant()->create();

        $this->actingAs($consultant)->post(route('clinical-notes.store', $visit), [
            'complaint' => 'Headache',
            'diagnosis' => 'Malaria',
            'treatment_notes' => 'Medication',
        ])->assertRedirect();
    }
}
