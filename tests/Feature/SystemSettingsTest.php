<?php

namespace Tests\Feature;

use App\Enums\AuditActionType;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_staff_cannot_access_system_settings(): void
    {
        $user = User::factory()->accounts()->create();

        $this->actingAs($user)
            ->get(route('system-settings.edit'))
            ->assertForbidden();
    }

    public function test_administrator_can_update_system_settings(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)->patch(route('system-settings.update'), [
            'name' => 'Updated Hospital Name',
            'section' => 'Updated Section',
            'system_name' => 'Updated Billing System',
            'session_lifetime_minutes' => 90,
            'large_deposit_threshold' => 5000,
            'low_balance_threshold' => 750,
        ])->assertRedirect(route('system-settings.edit'));

        $this->assertDatabaseHas('settings', [
            'key' => 'name',
            'value' => 'Updated Hospital Name',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action_type' => AuditActionType::SystemSettingsUpdated->value,
            'user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('system-settings.edit'))
            ->assertOk()
            ->assertSee('Updated Hospital Name');
    }

    public function test_updated_large_deposit_threshold_affects_deposit_validation(): void
    {
        $admin = User::factory()->administrator()->create();
        $accounts = User::factory()->accounts()->create();
        $member = Patient::factory()->member()->create();

        Setting::query()->create([
            'key' => 'large_deposit_threshold',
            'value' => '500',
        ]);
        app(SettingsService::class)->applyToConfig();

        $this->actingAs($accounts)->post(route('deposits.store'), [
            'patient_id' => $member->id,
            'amount' => 600,
            'deposit_date' => today()->toDateString(),
        ])->assertSessionHasErrors('confirm_large_deposit');
    }

    public function test_settings_service_merges_database_values_with_config_defaults(): void
    {
        Setting::query()->create([
            'key' => 'name',
            'value' => 'Database Hospital',
        ]);

        $settings = app(SettingsService::class)->all();

        $this->assertSame('Database Hospital', $settings['name']);
        $this->assertSame('High Cost Section', $settings['section']);
    }
}
