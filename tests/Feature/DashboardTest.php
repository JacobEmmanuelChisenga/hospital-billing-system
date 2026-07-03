<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\BillableServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BillableServiceSeeder::class);
    }

    public function test_registry_dashboard_shows_operations_charts(): void
    {
        $user = User::factory()->registry()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Operations Dashboard')
            ->assertSee('Daily Patient Flow')
            ->assertSee('Pending Actions Queue')
            ->assertSee('data-dashboard-chart', false);
    }

    public function test_nurse_dashboard_shows_clinical_charts(): void
    {
        $user = User::factory()->nurse()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Clinical Dashboard')
            ->assertSee('Patients Seen Today')
            ->assertSee('Common Diagnoses')
            ->assertSee('data-dashboard-chart', false);
    }

    public function test_accounts_dashboard_shows_financial_charts(): void
    {
        $user = User::factory()->accounts()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Financial Dashboard')
            ->assertSee('Daily Revenue Breakdown')
            ->assertSee('Deposits vs Spending')
            ->assertSee('data-dashboard-chart', false);
    }

    public function test_admin_dashboard_shows_system_charts(): void
    {
        $user = User::factory()->administrator()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('System Dashboard')
            ->assertSee('System Activity')
            ->assertSee('User Activity')
            ->assertSee('data-dashboard-chart', false);
    }
}
