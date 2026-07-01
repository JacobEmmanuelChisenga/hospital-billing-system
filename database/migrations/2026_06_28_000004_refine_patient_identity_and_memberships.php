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
            $table->string('patient_number')->nullable()->unique()->after('id');
            $table->string('first_name')->nullable()->after('type');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('gender')->nullable()->after('last_name');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('nationality')->nullable()->after('nrc_number');
            $table->string('marital_status')->nullable()->after('nationality');
            $table->string('alternative_phone')->nullable()->after('phone_number');
            $table->string('email')->nullable()->after('alternative_phone');
            $table->string('town_city')->nullable()->after('contact_address');
            $table->string('department')->nullable()->after('man_number');
            $table->string('employment_status')->nullable()->after('department');
        });

        $patients = DB::table('patients')->orderBy('id')->get();

        foreach ($patients as $patient) {
            $parts = preg_split('/\s+/', trim((string) $patient->name)) ?: [];
            $firstName = $parts[0] ?? 'Unknown';
            $lastName = count($parts) > 1 ? array_pop($parts) : $firstName;
            $middleName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

            DB::table('patients')->where('id', $patient->id)->update([
                'patient_number' => 'RR-'.str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
            ]);
        }

        Schema::create('memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('membership_number')->unique();
            $table->string('status')->index();
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });

        $members = DB::table('patients')->where('type', 'member')->orderBy('id')->get();

        foreach ($members as $member) {
            DB::table('memberships')->insert([
                'patient_id' => $member->id,
                'membership_number' => $member->hc_number ?: 'HC-'.str_pad((string) $member->id, 6, '0', STR_PAD_LEFT),
                'status' => $member->membership_status ?? 'pending_payment',
                'start_date' => null,
                'expiry_date' => $member->membership_valid_until,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');

        Schema::table('patients', function (Blueprint $table): void {
            $table->dropColumn([
                'patient_number',
                'first_name',
                'middle_name',
                'last_name',
                'gender',
                'date_of_birth',
                'nationality',
                'marital_status',
                'alternative_phone',
                'email',
                'town_city',
                'department',
                'employment_status',
            ]);
        });
    }
};
