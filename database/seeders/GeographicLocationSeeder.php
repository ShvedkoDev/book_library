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
            ['location_type' => 'state', 'name' => 'Chuuk State', 'parent_id' => null, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'state', 'name' => 'Kosrae State', 'parent_id' => null, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'state', 'name' => 'Yap State', 'parent_id' => null, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'state', 'name' => 'Pohnpei State', 'parent_id' => null, 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Islands (children of states)
            // Chuuk islands
            ['location_type' => 'island', 'name' => 'Chuuk Lagoon', 'parent_id' => 1, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Kosrae islands
            ['location_type' => 'island', 'name' => 'Kosrae', 'parent_id' => 2, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Yap islands
            ['location_type' => 'island', 'name' => 'Yap', 'parent_id' => 3, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Pohnpei islands
            ['location_type' => 'island', 'name' => 'Pohnpei', 'parent_id' => 4, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('geographic_locations')->insert($locations);
    }
}
