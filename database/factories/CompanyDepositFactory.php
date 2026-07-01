<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyDeposit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyDeposit>
 */
class CompanyDepositFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'deposit_date' => now()->toDateString(),
            'reference' => fake()->optional()->bothify('CDEP-####'),
            'notes' => null,
            'created_by' => User::factory()->accounts(),
            'reversed_at' => null,
            'reversed_by' => null,
            'reversal_reason' => null,
        ];
    }
}
