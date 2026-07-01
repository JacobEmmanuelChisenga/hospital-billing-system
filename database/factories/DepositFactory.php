<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Deposit;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deposit>
 */
class DepositFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory()->member(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'deposit_date' => now()->toDateString(),
            'reference' => fake()->optional()->bothify('DEP-####'),
            'notes' => null,
            'created_by' => User::factory()->accounts(),
            'reversed_at' => null,
            'reversed_by' => null,
            'reversal_reason' => null,
        ];
    }
}
