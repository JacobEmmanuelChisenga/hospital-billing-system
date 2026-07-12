<?php

use App\Models\Patient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Patient::query()
            ->whereNull('file_number')
            ->orderBy('id')
            ->chunkById(100, function ($patients): void {
                foreach ($patients as $patient) {
                    $patient->forceFill([
                        'file_number' => config('hospital.file_number_prefix', 'RRGH').'-'.str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT),
                    ])->saveQuietly();
                }
            });

        Schema::table('patients', function (Blueprint $table): void {
            $table->dropIndex(['file_number']);
            $table->unique('file_number');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropUnique(['file_number']);
            $table->index('file_number');
        });
    }
};
