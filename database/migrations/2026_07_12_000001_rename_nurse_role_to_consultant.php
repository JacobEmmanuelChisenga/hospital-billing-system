<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'nurse')
            ->update(['role' => 'consultant']);

        DB::table('users')
            ->where('email', 'nurse@ronaldross.local')
            ->update([
                'email' => 'consultant@ronaldross.local',
                'name' => 'Consultant',
            ]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'consultant')
            ->update(['role' => 'nurse']);

        DB::table('users')
            ->where('email', 'consultant@ronaldross.local')
            ->update([
                'email' => 'nurse@ronaldross.local',
                'name' => 'Nursing Officer',
            ]);
    }
};
