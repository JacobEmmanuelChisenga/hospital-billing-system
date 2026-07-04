<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('account_type', 20);
            $table->unsignedBigInteger('account_id');
            $table->string('transaction_type', 30);
            $table->string('reference', 50)->nullable();
            $table->nullableMorphs('related');
            $table->string('description');
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->decimal('running_balance', 14, 2);
            $table->date('transaction_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['account_type', 'account_id', 'transaction_date', 'id'], 'account_ledgers_account_date_idx');
            $table->index(['transaction_type', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_ledgers');
    }
};
