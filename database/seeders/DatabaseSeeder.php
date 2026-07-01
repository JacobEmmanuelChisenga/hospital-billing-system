<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        User::updateOrCreate(
            ['email' => 'admin@ronaldross.local'],
            [
                'name' => 'System Administrator',
                'password' => $defaultPassword,
                'role' => UserRole::Administrator,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'accounts@ronaldross.local'],
            [
                'name' => 'Accounts Officer',
                'password' => $defaultPassword,
                'role' => UserRole::Accounts,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'registry@ronaldross.local'],
            [
                'name' => 'Registry Clerk',
                'password' => $defaultPassword,
                'role' => UserRole::Registry,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'nurse@ronaldross.local'],
            [
                'name' => 'Nursing Officer',
                'password' => $defaultPassword,
                'role' => UserRole::Nurse,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );

        $this->call(BillableServiceSeeder::class);
    }
}
