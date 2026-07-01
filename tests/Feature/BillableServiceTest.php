<?php

namespace Tests\Feature;

use App\Enums\AuditActionType;
use App\Enums\ChargeCategory;
use App\Enums\VisitStatus;
use App\Enums\VisitType;
use App\Models\BillableService;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\BillableServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillableServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BillableServiceSeeder::class);
    }

    public function test_registry_clerk_cannot_access_service_catalogue(): void
    {
        $user = User::factory()->registry()->create();

        $this->actingAs($user)
            ->get(route('billable-services.index'))
            ->assertForbidden();
    }

    public function test_accounts_staff_cannot_access_service_catalogue(): void
    {
        $user = User::factory()->accounts()->create();

        $this->actingAs($user)
            ->get(route('billable-services.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_view_service_catalogue(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('billable-services.index'))
            ->assertOk()
            ->assertSee('Service Catalogue')
            ->assertSee('Consultation');
    }

    public function test_administrator_can_add_service(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->post(route('billable-services.store'), [
            'name' => 'ECG',
            'category' => ChargeCategory::Procedure->value,
            'price' => 350,
            'is_active' => '1',
        ])->assertRedirect(route('billable-services.index'));

        $this->assertDatabaseHas('billable_services', [
            'name' => 'ECG',
            'category' => ChargeCategory::Procedure->value,
            'price' => 350,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action_type' => AuditActionType::BillableServiceCreated->value,
            'user_id' => $admin->id,
        ]);
    }

    public function test_administrator_can_update_service_price(): void
    {
        $admin = User::factory()->administrator()->create();
        $service = BillableService::query()->where('name', 'Consultation')->firstOrFail();

        $this->actingAs($admin)->patch(route('billable-services.update', $service), [
            'name' => 'Consultation',
            'category' => ChargeCategory::Consultation->value,
            'price' => 200,
            'is_active' => '1',
        ])->assertRedirect(route('billable-services.edit', $service));

        $this->assertDatabaseHas('billable_services', [
            'id' => $service->id,
            'price' => 200,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action_type' => AuditActionType::BillableServiceUpdated->value,
            'user_id' => $admin->id,
        ]);
    }

    public function test_inactive_service_is_hidden_from_visit_charge_form(): void
    {
        $registry = User::factory()->registry()->create();
        $patient = Patient::factory()->member()->create([
            'balance' => 5000,
        ]);
        $visit = Visit::query()->create([
            'patient_id' => $patient->id,
            'visit_date' => now()->toDateString(),
            'visit_type' => VisitType::Opd,
            'status' => VisitStatus::AwaitingBilling,
            'opened_by' => $registry->id,
        ]);
        $service = BillableService::query()->where('name', 'Consultation')->firstOrFail();

        $this->actingAs(User::factory()->administrator()->create())
            ->patch(route('billable-services.update', $service), [
                'name' => 'Consultation',
                'category' => ChargeCategory::Consultation->value,
                'price' => 150,
                'is_active' => '0',
            ]);

        $this->actingAs($registry)
            ->get(route('visits.show', $visit))
            ->assertOk()
            ->assertDontSee('Consultation —');
    }

    public function test_billable_service_seeder_is_idempotent(): void
    {
        $count = BillableService::query()->count();

        $this->seed(BillableServiceSeeder::class);

        $this->assertSame($count, BillableService::query()->count());
    }
}
