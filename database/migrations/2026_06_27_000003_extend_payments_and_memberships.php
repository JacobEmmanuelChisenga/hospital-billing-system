<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Membership validity is tracked on the patient so the profile can show
        // "Active / Valid Until" without re-querying every membership payment.
        Schema::table('patients', function (Blueprint $table): void {
            $table->date('membership_valid_until')->nullable()->after('status');
        });

        // Capture how the deposit cash was received for the receipt / cash book.
        Schema::table('deposits', function (Blueprint $table): void {
            $table->string('payment_method')->nullable()->after('amount');
        });

        // Membership payments now cover a single holder (a member joining the
        // scheme OR a dependant). The principal is only set for dependants.
        Schema::table('membership_fees', function (Blueprint $table): void {
            $table->foreignId('patient_id')->nullable()->after('id');
            $table->string('payment_method')->nullable()->after('amount');
            $table->string('reference')->nullable()->after('payment_method');
            $table->index('patient_id');
        });

        // Existing fees were recorded against dependants — treat the dependant
        // as the membership holder so historical records stay meaningful.
        DB::table('membership_fees')
            ->whereNull('patient_id')
            ->update(['patient_id' => DB::raw('dependant_patient_id')]);

        // Best-effort backfill of patient membership validity from recorded fees.
        $holders = DB::table('membership_fees')
            ->whereNotNull('patient_id')
            ->select('patient_id', DB::raw('MAX(expiry_date) as max_expiry'))
            ->groupBy('patient_id')
            ->get();

        foreach ($holders as $holder) {
            DB::table('patients')
                ->where('id', $holder->patient_id)
                ->update(['membership_valid_until' => $holder->max_expiry]);
        }

        // A member's own membership has no principal/dependant linkage.
        Schema::table('membership_fees', function (Blueprint $table): void {
            $table->foreignId('principal_patient_id')->nullable()->change();
            $table->foreignId('dependant_patient_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('membership_fees', function (Blueprint $table): void {
            $table->dropIndex(['patient_id']);
            $table->dropColumn(['patient_id', 'payment_method', 'reference']);
        });

        Schema::table('deposits', function (Blueprint $table): void {
            $table->dropColumn('payment_method');
        });

        Schema::table('patients', function (Blueprint $table): void {
            $table->dropColumn('membership_valid_until');
        });
    }
};
