<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Current shared pool for all company patients.
            // Every change is also recorded in company_deposits or bills.
            $table->decimal('balance', 12, 2)->default(0);

            $table->string('status')->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('name');
            $table->string('hc_number')->nullable()->unique();
            $table->string('man_number')->nullable()->index();
            $table->foreignId('company_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('principal_patient_id')->nullable()->constrained('patients')->restrictOnDelete();
            $table->string('relationship')->nullable();
            $table->string('file_number')->nullable()->index();
            $table->string('nrc_number')->nullable()->index();

            // Used for member accounts. Dependants use their principal member's balance;
            // company patients use the company balance.
            $table->decimal('balance', 12, 2)->default(0);

            $table->string('status')->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('deposit_date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            // Reversal fields keep the original deposit visible instead of deleting it.
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('reversal_reason')->nullable();

            $table->timestamps();
            $table->index(['patient_id', 'deposit_date']);
        });

        Schema::create('company_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('deposit_date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('reversal_reason')->nullable();

            $table->timestamps();
            $table->index(['company_id', 'deposit_date']);
        });

        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();

            // One of these identifies the account that paid the bill:
            // account_patient_id for members/principal members, company_id for company pools.
            $table->foreignId('account_patient_id')->nullable()->constrained('patients')->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->restrictOnDelete();

            $table->date('visit_date');
            $table->string('visit_type')->index();
            $table->string('ward_bed')->nullable();
            $table->decimal('consultation_amount', 12, 2)->default(0);
            $table->decimal('pharmacy_amount', 12, 2)->default(0);
            $table->decimal('lab_amount', 12, 2)->default(0);
            $table->decimal('ward_amount', 12, 2)->default(0);
            $table->decimal('other_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->text('notes')->nullable();
            $table->string('status')->default('posted')->index();
            $table->text('void_reason')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->timestamps();
            $table->index(['patient_id', 'visit_date']);
            $table->index(['account_patient_id', 'visit_date']);
            $table->index(['company_id', 'visit_date']);
        });

        Schema::create('membership_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('principal_patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('dependant_patient_id')->constrained('patients')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->date('expiry_date')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['principal_patient_id', 'expiry_date']);
            $table->index(['dependant_patient_id', 'expiry_date']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action_type')->index();
            $table->text('description');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Polymorphic reference to the record affected by the action.
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('membership_fees');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('company_deposits');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('companies');
    }
};
