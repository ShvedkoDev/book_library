<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@micronesianlib.edu',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Teacher Maria',
                'email' => 'maria.teacher@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Educator John',
                'email' => 'john.educator@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Professor Sarah',
                'email' => 'sarah.prof@uog.edu',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Librarian James',
                'email' => 'james.lib@fsmgov.org',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}
