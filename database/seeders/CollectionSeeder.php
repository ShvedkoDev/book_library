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
                'name' => 'Pacific Islands Mathematics Series',
                'description' => 'Comprehensive mathematics curriculum designed specifically for Pacific Island students, incorporating cultural contexts and real-world applications.',
                'is_series' => true,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Micronesian Science Fundamentals',
                'description' => 'Science textbook series covering biology, environmental science, and marine studies with focus on Pacific ecosystems.',
                'is_series' => true,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Traditional Stories Collection',
                'description' => 'Compilation of traditional folktales, legends, and oral histories from various Micronesian cultures.',
                'is_series' => false,
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Language Preservation Library',
                'description' => 'Educational materials focused on preserving and teaching indigenous languages of Micronesia.',
                'is_series' => false,
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pacific History Chronicles',
                'description' => 'Historical accounts and educational materials covering pre-colonial, colonial, and post-independence periods.',
                'is_series' => true,
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Marine Environment Studies',
                'description' => 'Educational resources focusing on coral reefs, marine biodiversity, and ocean conservation.',
                'is_series' => false,
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cultural Practices Manual',
                'description' => 'Guides and instructional materials for traditional crafts, navigation, and cultural ceremonies.',
                'is_series' => false,
                'sort_order' => 7,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Primary Reading Adventures',
                'description' => 'Leveled reading series for elementary students featuring local characters and settings.',
                'is_series' => true,
                'sort_order' => 8,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Island Geography Explorer',
                'description' => 'Geographic studies of Pacific islands including maps, climate, and physical features.',
                'is_series' => true,
                'sort_order' => 9,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Teacher Resource Toolkit',
                'description' => 'Professional development materials and classroom resources for Pacific Island educators.',
                'is_series' => false,
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('collections')->insert($collections);
    }
}
