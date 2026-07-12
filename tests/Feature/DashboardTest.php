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
            ->assertSee('Registered Today')
            ->assertSee('Daily Patient Flow')
            ->assertSee('Pending Workload')
            ->assertSee('Recent Registrations')
            ->assertSee('data-dashboard-chart', false);
    }

    public function test_consultant_dashboard_shows_clinical_charts(): void
    {
        $user = User::factory()->consultant()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Clinical Dashboard')
            ->assertSee('Patients Seen Today')
            ->assertSee('Top Diagnoses Today')
            ->assertSee('Today\'s Queue')
            ->assertSee('data-dashboard-chart', false);
    }

    public function test_accounts_dashboard_shows_financial_charts(): void
    {
        $user = User::factory()->accounts()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Financial Dashboard')
            ->assertSee("Today's Deposits")
            ->assertSee("Today's Billing")
            ->assertSee('Current Member Balances')
            ->assertSee('Daily Revenue Breakdown')
            ->assertSee('Deposits vs Billing Trend')
            ->assertSee('Recent Receipts')
            ->assertSee('data-dashboard-chart', false);
    }

    public function test_admin_dashboard_shows_system_charts(): void
    {
        $user = User::factory()->administrator()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Administrator Dashboard')
            ->assertSee('System Activity (This Week)')
            ->assertSee('User Activity by Role')
            ->assertSee('Audit Events Breakdown')
            ->assertSee('Recent Audit Logs')
            ->assertSee('View all audit logs')
            ->assertSee('System Uptime')
            ->assertSee('Audit Log', false)
            ->assertSee('Staff Users', false)
            ->assertSee('data-dashboard-chart', false);
    }
}
