<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TermsOfUseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $termsVersions = [
            [
                'version' => '1.0',
                'content' => 'Welcome to the Micronesian Teachers Digital Library. By accessing and using this digital library, you agree to the following terms:\n\n1. Educational Use: This library is intended for educational purposes only.\n2. Respect for Authors: All materials are protected by copyright and should be used respectfully.\n3. No Commercial Use: Materials may not be used for commercial purposes without permission.\n4. Attribution: When using materials, proper attribution should be given to authors and publishers.\n5. Community Guidelines: Users should maintain respectful behavior in reviews and interactions.\n\nFor questions about these terms, please contact the library administrators.',
                'is_active' => true,
                'effective_date' => now()->subDays(30),
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ],
        ];

        DB::table('terms_of_use_versions')->insert($termsVersions);

        // Insert user terms acceptance
        $userTermsAcceptance = [
            [
                'user_id' => 1, // Admin
                'terms_version_id' => 1,
                'accepted_at' => now()->subDays(30),
                'ip_address' => '192.168.1.1',
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ],
            [
                'user_id' => 2, // Teacher Maria
                'terms_version_id' => 1,
                'accepted_at' => now()->subDays(25),
                'ip_address' => '192.168.1.2',
                'created_at' => now()->subDays(25),
                'updated_at' => now()->subDays(25),
            ],
            [
                'user_id' => 3, // Educator John
                'terms_version_id' => 1,
                'accepted_at' => now()->subDays(20),
                'ip_address' => '192.168.1.3',
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subDays(20),
            ],
        ];

        DB::table('user_terms_acceptance')->insert($userTermsAcceptance);
    }
}
