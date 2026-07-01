<?php

namespace Tests\Feature;

use App\Enums\AuditActionType;
use App\Models\Company;
use App\Models\CompanyDeposit;
use App\Models\Deposit;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositsAndCompanyAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_nursing_staff_cannot_load_deposits(): void
    {
        $user = User::factory()->nurse()->create();

        $this->actingAs($user)
            ->get(route('deposits.index'))
            ->assertForbidden();
    }

    public function test_accounts_staff_can_load_member_deposit_and_update_balance(): void
    {
        $user = User::factory()->accounts()->create();
        $member = Patient::factory()->member()->create(['balance' => 500]);

        $response = $this->actingAs($user)->post(route('deposits.store'), [
            'patient_id' => $member->id,
            'amount' => 1500,
            'payment_method' => 'cash',
            'deposit_date' => now()->toDateString(),
            'reference' => 'DEP-001',
        ]);

        $deposit = Deposit::query()->first();

        $response->assertRedirect(route('deposits.show', $deposit));
        $this->assertSame('2000.00', $member->fresh()->balance);
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => AuditActionType::DepositCreated->value,
            'related_id' => $deposit->id,
        ]);
    }

    public function test_large_deposit_requires_confirmation(): void
    {
        $user = User::factory()->accounts()->create();
        $member = Patient::factory()->member()->create();

        $this->actingAs($user)->post(route('deposits.store'), [
            'patient_id' => $member->id,
            'amount' => 15000,
            'payment_method' => 'cash',
            'deposit_date' => now()->toDateString(),
        ])->assertSessionHasErrors('confirm_large_deposit');

        $this->assertSame('0.00', $member->fresh()->balance);
    }

    public function test_accounts_staff_can_reverse_member_deposit(): void
    {
        $user = User::factory()->accounts()->create();
        $member = Patient::factory()->member()->create(['balance' => 2000]);
        $deposit = Deposit::factory()->create([
            'patient_id' => $member->id,
            'amount' => 1500,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('deposits.reverse', $deposit), [
            'reversal_reason' => 'Incorrect amount entered by mistake.',
        ]);

        $response->assertRedirect(route('deposits.show', $deposit));
        $this->assertSame('500.00', $member->fresh()->balance);
        $this->assertNotNull($deposit->fresh()->reversed_at);
    }

    public function test_accounts_staff_can_load_company_deposit_and_update_pool_balance(): void
    {
        $user = User::factory()->accounts()->create();
        $company = Company::factory()->create(['balance' => 10000]);

        $response = $this->actingAs($user)->post(route('company-accounts.deposits.store', $company), [
            'amount' => 25000,
            'deposit_date' => now()->toDateString(),
            'reference' => 'CDEP-001',
            'confirm_large_deposit' => '1',
        ]);

        $response->assertRedirect(route('company-accounts.show', $company));
        $this->assertSame('35000.00', $company->fresh()->balance);
        $this->assertDatabaseHas('company_deposits', [
            'company_id' => $company->id,
            'amount' => 25000,
        ]);
    }

    public function test_accounts_staff_can_create_company_account(): void
    {
        $user = User::factory()->accounts()->create();

        $response = $this->actingAs($user)->post(route('company-accounts.store'), [
            'name' => 'Zambia Mining Corp',
            'contact_person' => 'John Banda',
            'phone' => '0977000000',
            'email' => 'accounts@zmc.test',
        ]);

        $company = Company::query()->where('name', 'Zambia Mining Corp')->first();

        $response->assertRedirect(route('company-accounts.show', $company));
        $this->assertDatabaseHas('companies', [
            'name' => 'Zambia Mining Corp',
            'balance' => 0,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => AuditActionType::CompanyCreated->value,
            'related_type' => Company::class,
            'related_id' => $company->id,
        ]);
    }

    public function test_nursing_staff_cannot_create_company_account(): void
    {
        $user = User::factory()->registry()->create();

        $this->actingAs($user)->post(route('company-accounts.store'), [
            'name' => 'Nurse Company',
        ])->assertForbidden();

        $this->assertDatabaseMissing('companies', ['name' => 'Nurse Company']);
    }

    public function test_accounts_staff_can_update_company_account_details(): void
    {
        $user = User::factory()->accounts()->create();
        $company = Company::factory()->create(['name' => 'Old Company Name']);

        $response = $this->actingAs($user)->patch(route('company-accounts.update', $company), [
            'name' => 'Updated Company Name',
            'contact_person' => 'Mary Banda',
            'phone' => '0977111222',
            'email' => 'mary@example.test',
        ]);

        $response->assertRedirect(route('company-accounts.show', $company));
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Updated Company Name',
            'contact_person' => 'Mary Banda',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => AuditActionType::CompanyUpdated->value,
            'related_id' => $company->id,
        ]);
    }

    public function test_accounts_staff_can_suspend_company_and_block_deposits(): void
    {
        $user = User::factory()->accounts()->create();
        $company = Company::factory()->create(['balance' => 1000]);

        $this->actingAs($user)
            ->post(route('company-accounts.suspend', $company))
            ->assertRedirect(route('company-accounts.show', $company));

        $this->assertSame('suspended', $company->fresh()->status);

        $this->actingAs($user)->post(route('company-accounts.deposits.store', $company), [
            'amount' => 500,
            'deposit_date' => now()->toDateString(),
        ])->assertSessionHas('error');

        $this->assertSame('1000.00', $company->fresh()->balance);
    }

    public function test_accounts_staff_can_reverse_company_deposit(): void
    {
        $user = User::factory()->accounts()->create();
        $company = Company::factory()->create(['balance' => 30000]);
        $deposit = CompanyDeposit::factory()->create([
            'company_id' => $company->id,
            'amount' => 10000,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('company-deposits.reverse', $deposit), [
            'reversal_reason' => 'Duplicate deposit posted in error today.',
        ]);

        $response->assertRedirect(route('company-accounts.show', $company));
        $this->assertSame('20000.00', $company->fresh()->balance);
        $this->assertNotNull($deposit->fresh()->reversed_at);
    }

    public function test_deposit_cannot_be_loaded_for_non_member(): void
    {
        $user = User::factory()->accounts()->create();
        $dependant = Patient::factory()->dependant()->create();

        $this->actingAs($user)->post(route('deposits.store'), [
            'patient_id' => $dependant->id,
            'amount' => 500,
            'payment_method' => 'cash',
            'deposit_date' => now()->toDateString(),
        ])->assertSessionHasErrors('patient_id');
    }
}
