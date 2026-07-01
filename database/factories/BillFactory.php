<?php

namespace Database\Factories;

use App\Enums\BillStatus;
use App\Enums\VisitType;
use App\Models\Bill;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bill>
 */
class BillFactory extends Factory
{
    public function definition(): array
    {
        $consultation = fake()->randomFloat(2, 50, 500);
        $pharmacy = fake()->randomFloat(2, 0, 1500);
        $lab = fake()->randomFloat(2, 0, 1000);
        $ward = fake()->randomFloat(2, 0, 2000);
        $other = fake()->randomFloat(2, 0, 500);

        return [
            'patient_id' => Patient::factory()->member(),
            'account_patient_id' => null,
            'company_id' => null,
            'visit_date' => now()->toDateString(),
            'visit_type' => fake()->randomElement(VisitType::cases()),
            'ward_bed' => null,
            'consultation_amount' => $consultation,
            'pharmacy_amount' => $pharmacy,
            'lab_amount' => $lab,
            'ward_amount' => $ward,
            'other_amount' => $other,
            'total_amount' => $consultation + $pharmacy + $lab + $ward + $other,
            'notes' => null,
            'status' => BillStatus::Posted,
            'void_reason' => null,
            'voided_at' => null,
            'voided_by' => null,
            'created_by' => User::factory()->nursing(),
        ];
    }
}
