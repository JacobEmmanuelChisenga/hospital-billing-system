<?php

namespace Database\Factories;

use App\Enums\AuditActionType;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'action_type' => fake()->randomElement(AuditActionType::cases()),
            'description' => fake()->sentence(),
            'user_id' => User::factory(),
            'related_type' => null,
            'related_id' => null,
            'metadata' => null,
        ];
    }
}
