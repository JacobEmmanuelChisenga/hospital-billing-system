<?php

namespace Tests\Feature;

use App\Enums\AuditActionType;
use App\Models\MembershipFee;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipFeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_nursing_staff_cannot_access_membership_payments(): void
    {
        $user = User::factory()->nurse()->create();

        $this->actingAs($user)
            ->get(route('membership-fees.index'))
            ->assertForbidden();
    }

    public function test_accounts_staff_can_record_membership_payment_for_member(): void
    {
        $user = User::factory()->accounts()->create();
        $member = Patient::factory()->member()->create(['balance' => 0]);

        $response = $this->actingAs($user)->post(route('membership-fees.store'), [
            'patient_id' => $member->id,
            'amount' => 600,
            'payment_method' => 'mobile_money',
            'reference' => 'MEM-001',
            'payment_date' => today()->toDateString(),
            'expiry_date' => today()->addYear()->toDateString(),
        ]);

        $fee = MembershipFee::query()->first();

        $response->assertRedirect(route('membership-fees.show', $fee));
        $this->assertDatabaseHas('membership_fees', [
            'patient_id' => $member->id,
            'principal_patient_id' => null,
            'amount' => 600,
            'payment_method' => 'mobile_money',
        ]);

        // Membership payments must not add spendable treatment balance.
        $this->assertSame('0.00', $member->fresh()->balance);

        // Membership is activated with the expiry date.
        $this->assertSame(
            today()->addYear()->toDateString(),
            $member->fresh()->membership_valid_until->toDateString(),
        );

        $this->assertDatabaseHas('audit_logs', [
            'action_type' => AuditActionType::MembershipFeeRecorded->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_accounts_staff_can_record_membership_payment_for_dependant(): void
    {
        $user = User::factory()->accounts()->create();
        $principal = Patient::factory()->member()->create();
        $dependant = Patient::factory()->dependant($principal)->create();

        $this->actingAs($user)->post(route('membership-fees.store'), [
            'patient_id' => $dependant->id,
            'amount' => 500,
            'payment_method' => 'cash',
            'payment_date' => today()->toDateString(),
            'expiry_date' => today()->addYear()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseHas('membership_fees', [
            'patient_id' => $dependant->id,
            'principal_patient_id' => $principal->id,
            'amount' => 500,
        ]);

        $this->assertTrue($dependant->fresh()->membershipIsActive());
    }

    public function test_company_patient_cannot_receive_membership_payment(): void
    {
        $user = User::factory()->accounts()->create();
        $companyPatient = Patient::factory()->companyPatient()->create();

        $this->actingAs($user)->post(route('membership-fees.store'), [
            'patient_id' => $companyPatient->id,
            'amount' => 500,
            'payment_method' => 'cash',
            'payment_date' => today()->toDateString(),
            'expiry_date' => today()->addYear()->toDateString(),
        ])->assertSessionHasErrors('patient_id');

        $this->assertDatabaseCount('membership_fees', 0);
    }

    public function test_payment_method_is_required(): void
    {
        $user = User::factory()->accounts()->create();
        $member = Patient::factory()->member()->create();

        $this->actingAs($user)->post(route('membership-fees.store'), [
            'patient_id' => $member->id,
            'amount' => 500,
            'payment_date' => today()->toDateString(),
            'expiry_date' => today()->addYear()->toDateString(),
        ])->assertSessionHasErrors('payment_method');
    }

    public function test_membership_payment_receipt_is_viewable(): void
    {
        $user = User::factory()->accounts()->create();
        $fee = MembershipFee::factory()->create();

        $this->actingAs($user)
            ->get(route('membership-fees.receipt', $fee))
            ->assertOk()
            ->assertSee('MEMBERSHIP RECEIPT');
    }

    public function test_membership_payment_list_can_filter_by_status(): void
    {
        $user = User::factory()->accounts()->create();

        MembershipFee::factory()->create([
            'expiry_date' => today()->addMonths(6),
            'created_by' => $user->id,
        ]);

        MembershipFee::factory()->create([
            'expiry_date' => today()->subDay(),
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('membership-fees.index', ['status' => 'expired']))
            ->assertOk()
            ->assertSee('Expired');
    }
}
