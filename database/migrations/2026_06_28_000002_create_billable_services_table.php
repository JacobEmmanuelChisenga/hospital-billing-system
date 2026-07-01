<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billable_services', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->index();
            $table->decimal('price', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('charge_lines', function (Blueprint $table): void {
            $table->foreignId('billable_service_id')->nullable()->after('visit_id')->constrained()->nullOnDelete();
        });

        DB::table('visits')->where('status', 'open')->update(['status' => 'ready_for_consultation']);
        DB::table('visits')->where('status', 'closed')->update(['status' => 'completed']);
    }

    public function down(): void
    {
        Schema::table('charge_lines', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('billable_service_id');
        });

        Schema::dropIfExists('billable_services');
    }
};
