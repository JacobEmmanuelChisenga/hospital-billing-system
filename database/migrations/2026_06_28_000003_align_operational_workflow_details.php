<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->string('phone_number')->nullable()->after('nrc_number');
            $table->string('contact_address')->nullable()->after('phone_number');
            $table->string('next_of_kin_name')->nullable()->after('contact_address');
            $table->string('next_of_kin_phone')->nullable()->after('next_of_kin_name');
            $table->string('next_of_kin_relationship')->nullable()->after('next_of_kin_phone');
        });

        Schema::table('clinical_notes', function (Blueprint $table): void {
            $table->text('examination_findings')->nullable()->after('vitals');
            $table->text('procedures_performed')->nullable()->after('treatment_notes');
        });

        DB::table('visits')->where('status', 'closed')->update(['status' => 'completed']);
    }

    public function down(): void
    {
        DB::table('visits')->where('status', 'completed')->update(['status' => 'closed']);

        Schema::table('clinical_notes', function (Blueprint $table): void {
            $table->dropColumn(['examination_findings', 'procedures_performed']);
        });

        Schema::table('patients', function (Blueprint $table): void {
            $table->dropColumn([
                'phone_number',
                'contact_address',
                'next_of_kin_name',
                'next_of_kin_phone',
                'next_of_kin_relationship',
            ]);
        });
    }
};
