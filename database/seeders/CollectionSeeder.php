<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $collections = [
            [
                'name' => 'PALM trial',
                'description' => 'Pacific Area Language Materials trial version booklets - literacy development readers for Micronesian languages',
                'is_series' => true,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PALM final',
                'description' => 'Pacific Area Language Materials final version booklets - published literacy materials for Micronesian languages',
                'is_series' => true,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PALM CD',
                'description' => 'Pacific Area Language Materials CD-ROM collection - digitized educational materials from the 1999 PALM CD-ROM compilation',
                'is_series' => true,
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('collections')->insert($collections);
    }
}
