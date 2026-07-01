<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\MembershipFee;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipFee>
 */
class MembershipFeeFactory extends Factory
{
    public function definition(): array
    {
        // By default a membership payment covers a dependant under a principal member.
        $principal = Patient::factory()->member()->create();
        $dependant = Patient::factory()->dependant($principal)->create();

        return [
            'patient_id' => $dependant->id,
            'principal_patient_id' => $principal->id,
            'dependant_patient_id' => $dependant->id,
            'amount' => fake()->randomFloat(2, 100, 1000),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'reference' => fake()->optional()->bothify('MEM-####'),
            'payment_date' => now()->toDateString(),
            'expiry_date' => now()->addYear()->toDateString(),
            'notes' => null,
            'created_by' => User::factory()->accounts(),
        ];
    }

    /** A membership payment for a member joining the scheme (no principal). */
    public function forMember(?Patient $member = null): static
    {
        $member ??= Patient::factory()->member()->create();

        return $this->state(fn (): array => [
            'patient_id' => $member->id,
            'principal_patient_id' => null,
            'dependant_patient_id' => null,
        ]);
    }
}
