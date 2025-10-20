<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeographicLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            // States (top level)
            ['location_type' => 'state', 'name' => 'Chuuk', 'parent_id' => null, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'state', 'name' => 'Pohnpei', 'parent_id' => null, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'state', 'name' => 'Yap', 'parent_id' => null, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'state', 'name' => 'Kosrae', 'parent_id' => null, 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Republic of the Marshall Islands
            ['location_type' => 'state', 'name' => 'Marshall Islands', 'parent_id' => null, 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Republic of Palau
            ['location_type' => 'state', 'name' => 'Palau', 'parent_id' => null, 'sort_order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Northern Mariana Islands
            ['location_type' => 'state', 'name' => 'Northern Mariana Islands', 'parent_id' => null, 'sort_order' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Guam
            ['location_type' => 'state', 'name' => 'Guam', 'parent_id' => null, 'sort_order' => 8, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Major Islands (children of states) - examples
            // Chuuk islands
            ['location_type' => 'island', 'name' => 'Weno', 'parent_id' => 1, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Tonoas', 'parent_id' => 1, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Pohnpei islands
            ['location_type' => 'island', 'name' => 'Pohnpei Island', 'parent_id' => 2, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Yap islands
            ['location_type' => 'island', 'name' => 'Yap Proper', 'parent_id' => 3, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Ulithi', 'parent_id' => 3, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Kosrae islands
            ['location_type' => 'island', 'name' => 'Kosrae Island', 'parent_id' => 4, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Marshall Islands
            ['location_type' => 'island', 'name' => 'Majuro', 'parent_id' => 5, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Kwajalein', 'parent_id' => 5, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Ebeye', 'parent_id' => 5, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Palau
            ['location_type' => 'island', 'name' => 'Koror', 'parent_id' => 6, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Babeldaob', 'parent_id' => 6, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Northern Mariana Islands
            ['location_type' => 'island', 'name' => 'Saipan', 'parent_id' => 7, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Tinian', 'parent_id' => 7, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Rota', 'parent_id' => 7, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('geographic_locations')->insert($locations);
    }
}
