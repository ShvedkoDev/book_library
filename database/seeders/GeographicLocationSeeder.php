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
            // ============================================================
            // STATES (top level)
            // ============================================================
            ['location_type' => 'state', 'name' => 'Chuuk State', 'parent_id' => null, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'state', 'name' => 'Kosrae State', 'parent_id' => null, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'state', 'name' => 'Pohnpei State', 'parent_id' => null, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'state', 'name' => 'Yap State', 'parent_id' => null, 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // ============================================================
            // ISLANDS (children of states)
            // ============================================================

            // Chuuk State Islands (parent_id = 1)
            ['location_type' => 'island', 'name' => 'Chuuk Lagoon', 'parent_id' => 1, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Kosrae State Islands (parent_id = 2)
            ['location_type' => 'island', 'name' => 'Kosrae', 'parent_id' => 2, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Pohnpei State Islands (parent_id = 3)
            ['location_type' => 'island', 'name' => 'Pohnpei', 'parent_id' => 3, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Yap State Islands (parent_id = 4)
            ['location_type' => 'island', 'name' => 'Elato', 'parent_id' => 4, 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Fais', 'parent_id' => 4, 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Faraulep', 'parent_id' => 4, 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Ifalik', 'parent_id' => 4, 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Lamotrek', 'parent_id' => 4, 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Ulithi', 'parent_id' => 4, 'sort_order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Woleai', 'parent_id' => 4, 'sort_order' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['location_type' => 'island', 'name' => 'Yap', 'parent_id' => 4, 'sort_order' => 8, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('geographic_locations')->insert($locations);
    }
}
