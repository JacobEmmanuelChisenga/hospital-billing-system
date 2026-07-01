<?php

namespace Tests\Feature;

use App\Enums\AuditActionType;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_nursing_staff_cannot_view_audit_log(): void
    {
        $user = User::factory()->nurse()->create();

        $this->actingAs($user)
            ->get(route('audit-logs.index'))
            ->assertForbidden();
    }

    public function test_accounts_staff_cannot_view_audit_log(): void
    {
        $user = User::factory()->accounts()->create();

        $this->actingAs($user)
            ->get(route('audit-logs.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_view_audit_log(): void
    {
        $user = User::factory()->administrator()->create();

        AuditLog::factory()->create([
            'action_type' => AuditActionType::PatientCreated,
            'description' => 'Registered member account for Test Patient.',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('audit-logs.index'))
            ->assertOk()
            ->assertSee('Audit Log')
            ->assertSee('Registered member account for Test Patient.');
    }

    public function test_audit_log_can_be_filtered_by_action_type(): void
    {
        $user = User::factory()->administrator()->create();

        AuditLog::factory()->create([
            'action_type' => AuditActionType::BillCreated,
            'description' => 'Posted bill for member.',
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        AuditLog::factory()->create([
            'action_type' => AuditActionType::DepositCreated,
            'description' => 'Loaded member deposit.',
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('audit-logs.index', [
                'preset' => 'today',
                'action_type' => AuditActionType::BillCreated->value,
            ]))
            ->assertOk()
            ->assertSee('Posted bill for member.')
            ->assertDontSee('Loaded member deposit.');
    }

    public function test_audit_log_detail_page_shows_metadata(): void
    {
        $user = User::factory()->administrator()->create();
        $patient = Patient::factory()->member()->create(['name' => 'Audit Patient']);

        $log = AuditLog::factory()->create([
            'action_type' => AuditActionType::PatientCreated,
            'description' => 'Registered member account for Audit Patient.',
            'user_id' => $user->id,
            'related_type' => Patient::class,
            'related_id' => $patient->id,
            'metadata' => ['hc_number' => 'HC-100'],
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('audit-logs.show', $log))
            ->assertOk()
            ->assertSee('Audit Patient')
            ->assertSee('HC-100');
    }

    public function test_patient_registration_writes_audit_entry(): void
    {
        $user = User::factory()->registry()->create();

        $this->actingAs($user)
            ->post(route('patients.store'), [
                'type' => 'member',
                'first_name' => 'Audited',
                'last_name' => 'Member',
                'gender' => 'female',
                'date_of_birth' => '1992-01-01',
                'nrc_number' => '920101/10/1',
                'nationality' => 'Zambian',
                'marital_status' => 'single',
                'phone_number' => '0977000000',
                'contact_address' => 'Plot 10, Lusaka',
                'town_city' => 'Lusaka',
                'next_of_kin_name' => 'Kin Member',
                'next_of_kin_phone' => '0966000000',
                'next_of_kin_relationship' => 'Parent',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action_type' => AuditActionType::PatientCreated->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_audit_log_csv_export_downloads(): void
    {
        $user = User::factory()->administrator()->create();

        $response = $this->actingAs($user)
            ->get(route('audit-logs.export', ['preset' => 'today']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }
}
