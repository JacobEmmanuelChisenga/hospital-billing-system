<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\BillableServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BillableServiceSeeder::class);
    }

    public function test_registry_clerk_can_access_charge_workflow_routes(): void
    {
        $user = User::factory()->registry()->create();

        $this->actingAs($user)->get(route('charges.pending'))->assertOk();
        $this->actingAs($user)->get(route('charges.post'))->assertOk();
        $this->actingAs($user)->get(route('charges.history'))->assertOk();
        $this->actingAs($user)->get(route('patients.search'))->assertOk();
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Registered Today');
    }

    public function test_consultant_can_access_consultant_workflow_routes(): void
    {
        $user = User::factory()->consultant()->create();

        $this->actingAs($user)->get(route('consultant.queue'))->assertOk();
        $this->actingAs($user)->get(route('consultant.active'))->assertOk();
        $this->actingAs($user)->get(route('consultant.consultations'))->assertOk();
        $this->actingAs($user)->get(route('patients.search'))->assertOk();
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Clinical Dashboard');
    }

    public function test_consultant_cannot_access_registry_charge_routes(): void
    {
        $user = User::factory()->consultant()->create();

        $this->actingAs($user)->get(route('charges.pending'))->assertForbidden();
        $this->actingAs($user)->get(route('patients.create'))->assertForbidden();
    }

    public function test_registry_clerk_cannot_access_consultant_workflow_routes(): void
    {
        $user = User::factory()->registry()->create();

        $this->actingAs($user)->get(route('consultant.queue'))->assertForbidden();
    }

    public function test_member_registration_link_preselects_patient_type(): void
    {
        $user = User::factory()->registry()->create();

        $this->actingAs($user)
            ->get(route('patients.create', ['type' => 'member']))
            ->assertOk()
            ->assertSee('Register Member')
            ->assertSee('value="member"', false);
    }
}
