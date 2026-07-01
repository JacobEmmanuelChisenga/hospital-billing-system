<?php

namespace Tests\Feature;

use App\Enums\BillStatus;
use App\Enums\VisitType;
use App\Models\Bill;
use App\Models\Company;
use App\Models\Deposit;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_staff_can_view_reports_dashboard(): void
    {
        $user = User::factory()->accounts()->create();

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Reports');
    }

    public function test_nursing_staff_cannot_view_reports(): void
    {
        $user = User::factory()->nurse()->create();

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('reports.member-accounts'))
            ->assertForbidden();
    }

    public function test_accounts_staff_can_view_member_accounts_report(): void
    {
        $user = User::factory()->accounts()->create();
        Patient::factory()->member()->create(['name' => 'Report Member']);

        $this->actingAs($user)
            ->get(route('reports.member-accounts'))
            ->assertOk()
            ->assertSee('Report Member');
    }

    public function test_summary_report_reflects_period_activity(): void
    {
        $user = User::factory()->accounts()->create();
        $member = Patient::factory()->member()->create();

        Deposit::factory()->create([
            'patient_id' => $member->id,
            'amount' => 1000,
            'deposit_date' => today(),
            'created_by' => $user->id,
        ]);

        Bill::factory()->create([
            'patient_id' => $member->id,
            'account_patient_id' => $member->id,
            'visit_date' => today(),
            'visit_type' => VisitType::Opd,
            'total_amount' => 250,
            'consultation_amount' => 250,
            'status' => BillStatus::Posted,
            'created_by' => User::factory()->nursing()->create()->id,
        ]);

        $this->actingAs($user)
            ->get(route('reports.index', ['preset' => 'today']))
            ->assertOk()
            ->assertSee('K 1,000.00')
            ->assertSee('K 250.00');
    }

    public function test_patient_statement_shows_deposits_and_bills(): void
    {
        $user = User::factory()->accounts()->create();
        $member = Patient::factory()->member()->create(['balance' => 750, 'name' => 'Statement Patient']);

        Deposit::factory()->create([
            'patient_id' => $member->id,
            'amount' => 1000,
            'deposit_date' => today(),
            'created_by' => $user->id,
        ]);

        Bill::factory()->create([
            'patient_id' => $member->id,
            'account_patient_id' => $member->id,
            'visit_date' => today(),
            'visit_type' => VisitType::Opd,
            'total_amount' => 250,
            'consultation_amount' => 250,
            'status' => BillStatus::Posted,
            'created_by' => User::factory()->nursing()->create()->id,
        ]);

        $this->actingAs($user)
            ->get(route('reports.patient-statement', ['patient' => $member, 'preset' => 'today']))
            ->assertOk()
            ->assertSee('Statement Patient')
            ->assertSee('Deposit')
            ->assertSee('OPD visit');
    }

    public function test_transactions_csv_export_downloads(): void
    {
        $user = User::factory()->accounts()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.transactions.export', ['preset' => 'today']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_company_report_shows_pool_usage(): void
    {
        $user = User::factory()->accounts()->create();
        $company = Company::factory()->create(['name' => 'Test Corp', 'balance' => 15000]);
        $patient = Patient::factory()->companyPatient($company)->create();

        Bill::factory()->create([
            'patient_id' => $patient->id,
            'company_id' => $company->id,
            'visit_date' => today(),
            'total_amount' => 500,
            'consultation_amount' => 500,
            'status' => BillStatus::Posted,
            'created_by' => User::factory()->nursing()->create()->id,
        ]);

        $this->actingAs($user)
            ->get(route('reports.companies.show', ['company' => $company, 'preset' => 'today']))
            ->assertOk()
            ->assertSee('Test Corp')
            ->assertSee($patient->name);
    }
}
