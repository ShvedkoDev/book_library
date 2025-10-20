<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publishers = [
            [
                'name' => 'UH Social Science Research Institute (SSRI), University of Hawaii at Manoa',
                'program_name' => 'Pacific Area Language Materials Development Center',
                'address' => 'University of Hawaii at Manoa
Honolulu, HI 96822
USA',
                'website' => 'https://www.hawaii.edu/ssri/',
                'contact_email' => 'ssri@hawaii.edu',
                'established_year' => 1975,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('publishers')->insert($publishers);
    }
}
