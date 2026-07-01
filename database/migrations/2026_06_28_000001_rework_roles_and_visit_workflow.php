<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'nursing')->update(['role' => 'registry']);

        Schema::table('patients', function (Blueprint $table): void {
            $table->string('membership_status')->default('not_applicable')->after('membership_valid_until');
        });

        Schema::create('visits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->date('visit_date');
            $table->string('visit_type')->index();
            $table->string('ward_bed')->nullable();
            $table->string('status')->default('registered')->index();
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('bill_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'visit_date']);
        });

        Schema::create('clinical_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('visit_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->text('complaint')->nullable();
            $table->text('vitals')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_notes')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('charge_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->string('category')->index();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::table('bills', function (Blueprint $table): void {
            $table->foreignId('visit_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
        });

        $bills = DB::table('bills')->orderBy('id')->get();

        foreach ($bills as $bill) {
            $visitId = DB::table('visits')->insertGetId([
                'patient_id' => $bill->patient_id,
                'visit_date' => $bill->visit_date,
                'visit_type' => $bill->visit_type,
                'ward_bed' => $bill->ward_bed,
                'status' => $bill->status === 'voided' ? 'cancelled' : 'completed',
                'opened_by' => $bill->created_by,
                'bill_id' => $bill->id,
                'completed_at' => $bill->created_at,
                'notes' => $bill->notes,
                'created_at' => $bill->created_at,
                'updated_at' => $bill->updated_at,
            ]);

            DB::table('bills')->where('id', $bill->id)->update(['visit_id' => $visitId]);

            $chargeMap = [
                'consultation' => (float) $bill->consultation_amount,
                'lab' => (float) $bill->lab_amount,
                'pharmacy' => (float) $bill->pharmacy_amount,
                'ward' => (float) $bill->ward_amount,
                'other' => (float) $bill->other_amount,
            ];

            foreach ($chargeMap as $category => $amount) {
                if ($amount <= 0) {
                    continue;
                }

                DB::table('charge_lines')->insert([
                    'visit_id' => $visitId,
                    'category' => $category,
                    'description' => ucfirst($category).' charge',
                    'amount' => $amount,
                    'recorded_by' => $bill->created_by,
                    'created_at' => $bill->created_at,
                    'updated_at' => $bill->updated_at,
                ]);
            }
        }

        Schema::table('visits', function (Blueprint $table): void {
            $table->foreign('bill_id')->references('id')->on('bills')->nullOnDelete();
        });

        DB::table('patients')
            ->whereIn('type', ['member', 'dependant'])
            ->where('membership_status', 'not_applicable')
            ->update(['membership_status' => 'pending_payment']);

        DB::table('patients')
            ->whereIn('type', ['member', 'dependant'])
            ->whereNotNull('membership_valid_until')
            ->whereDate('membership_valid_until', '>=', now()->toDateString())
            ->update(['membership_status' => 'active']);
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table): void {
            $table->dropForeign(['bill_id']);
        });

        Schema::table('bills', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('visit_id');
        });

        Schema::dropIfExists('charge_lines');
        Schema::dropIfExists('clinical_notes');
        Schema::dropIfExists('visits');

        Schema::table('patients', function (Blueprint $table): void {
            $table->dropColumn('membership_status');
        });
    }
};
