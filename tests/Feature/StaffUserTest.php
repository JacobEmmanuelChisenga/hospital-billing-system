<?php

namespace Tests\Feature;

use App\Enums\AuditActionType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_staff_cannot_manage_staff_users(): void
    {
        $user = User::factory()->accounts()->create();

        $this->actingAs($user)
            ->get(route('staff-users.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_create_staff_user(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->post(route('staff-users.store'), [
            'name' => 'New Accounts Officer',
            'email' => 'new.accounts@ronaldross.local',
            'role' => UserRole::Accounts->value,
            'status' => UserStatus::Active->value,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'new.accounts@ronaldross.local',
            'role' => UserRole::Accounts->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action_type' => AuditActionType::UserCreated->value,
            'user_id' => $admin->id,
        ]);
    }

    public function test_administrator_can_update_staff_user_and_reset_password(): void
    {
        $admin = User::factory()->administrator()->create();
        $staff = User::factory()->accounts()->create(['name' => 'Old Name']);

        $this->actingAs($admin)->patch(route('staff-users.update', $staff), [
            'name' => 'Updated Name',
            'email' => $staff->email,
            'role' => UserRole::Nursing->value,
            'status' => UserStatus::Active->value,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $staff->refresh();
        $this->assertSame('Updated Name', $staff->name);
        $this->assertSame(UserRole::Nursing, $staff->role);
        $this->assertTrue(Hash::check('new-password', $staff->password));

        $this->assertDatabaseHas('audit_logs', [
            'action_type' => AuditActionType::UserUpdated->value,
            'user_id' => $admin->id,
        ]);
    }

    public function test_administrator_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->patch(route('staff-users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => UserRole::Administrator->value,
            'status' => UserStatus::Inactive->value,
        ])->assertSessionHasErrors('status');

        $this->assertTrue($admin->fresh()->isActive());
    }

    public function test_administrator_cannot_change_own_role(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->patch(route('staff-users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => UserRole::Accounts->value,
            'status' => UserStatus::Active->value,
        ])->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Administrator, $admin->fresh()->role);
    }
}
