<?php

namespace App\Services;

use App\Enums\AuditActionType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Reads and writes hospital settings with config fallbacks.
 *
 * Settings stored in the database override values from config/hospital.php
 * so administrators can change labels and thresholds without editing .env.
 */
class SettingsService
{
    /** @var list<string> */
    public const KEYS = [
        'name',
        'section',
        'system_name',
        'session_lifetime_minutes',
        'large_deposit_threshold',
        'low_balance_threshold',
    ];

    /** Current settings merged with config defaults. */
    public function all(): array
    {
        $defaults = config('hospital');
        $stored = Setting::query()->pluck('value', 'key');

        return [
            'name' => (string) ($stored['name'] ?? $defaults['name']),
            'section' => (string) ($stored['section'] ?? $defaults['section']),
            'system_name' => (string) ($stored['system_name'] ?? $defaults['system_name']),
            'session_lifetime_minutes' => (int) ($stored['session_lifetime_minutes'] ?? $defaults['session_lifetime_minutes']),
            'large_deposit_threshold' => (float) ($stored['large_deposit_threshold'] ?? $defaults['large_deposit_threshold']),
            'low_balance_threshold' => (float) ($stored['low_balance_threshold'] ?? $defaults['low_balance_threshold']),
        ];
    }

    /** Push resolved settings into the runtime config array. */
    public function applyToConfig(): void
    {
        config(['hospital' => array_merge(config('hospital'), $this->all())]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data, User $user): void
    {
        DB::transaction(function () use ($data, $user): void {
            foreach (self::KEYS as $key) {
                if (! array_key_exists($key, $data)) {
                    continue;
                }

                Setting::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => (string) $data[$key]],
                );
            }

            AuditLogger::log(
                AuditActionType::SystemSettingsUpdated,
                'Updated hospital system settings.',
                null,
                $this->all(),
            );
        });

        $this->applyToConfig();
    }
}
