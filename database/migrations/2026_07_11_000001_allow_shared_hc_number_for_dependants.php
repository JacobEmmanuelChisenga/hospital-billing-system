<?php

use App\Enums\PatientType;
use App\Models\Patient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropUnique(['hc_number']);
            $table->index('hc_number');
        });

        Patient::query()
            ->where('type', PatientType::Dependant->value)
            ->with(['principalMember.membership'])
            ->chunkById(100, function ($dependants): void {
                foreach ($dependants as $dependant) {
                    $membershipNumber = $dependant->principalMember?->membership?->membership_number
                        ?? $dependant->principalMember?->hc_number;

                    if (blank($membershipNumber)) {
                        continue;
                    }

                    $dependant->forceFill(['hc_number' => $membershipNumber])->saveQuietly();
                }
            });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropIndex(['hc_number']);
            $table->unique('hc_number');
        });
    }
};
