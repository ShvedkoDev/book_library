<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Purpose',
                'slug' => 'purpose',
                'description' => 'The primary educational purpose or goal of the resource',
                'allow_multiple' => true,
                'use_for_filtering' => true,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Genre',
                'slug' => 'genre',
                'description' => 'The literary or content genre classification',
                'allow_multiple' => true,
                'use_for_filtering' => true,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sub-genre',
                'slug' => 'sub-genre',
                'description' => 'More specific genre or content classification',
                'allow_multiple' => true,
                'use_for_filtering' => true,
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Type',
                'slug' => 'type',
                'description' => 'The format or type of educational material',
                'allow_multiple' => false,
                'use_for_filtering' => true,
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Themes/Uses',
                'slug' => 'themes-uses',
                'description' => 'Thematic content or intended use cases',
                'allow_multiple' => true,
                'use_for_filtering' => true,
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Learner Level',
                'slug' => 'learner-level',
                'description' => 'Target student grade or education level',
                'allow_multiple' => true,
                'use_for_filtering' => true,
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('classification_types')->insert($types);
    }
}
