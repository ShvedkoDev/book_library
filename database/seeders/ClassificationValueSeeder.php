<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassificationValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Look up classification type IDs dynamically by slug
        $typeIds = [
            'purpose' => DB::table('classification_types')->where('slug', 'purpose')->value('id'),
            'genre' => DB::table('classification_types')->where('slug', 'genre')->value('id'),
            'subgenre' => DB::table('classification_types')->where('slug', 'sub-genre')->value('id'), // Uses 'sub-genre' slug
            'type' => DB::table('classification_types')->where('slug', 'type')->value('id'),
            'themes' => DB::table('classification_types')->where('slug', 'themes-uses')->value('id'), // Uses 'themes-uses' slug
            'learner-level' => DB::table('classification_types')->where('slug', 'learner-level')->value('id'),
        ];

        $values = [
            // ============================================================
            // PURPOSE VALUES
            // ============================================================
            ['classification_type_id' => $typeIds['purpose'], 'value' => 'Concept materials', 'parent_id' => null, 'description' => 'Basic concepts and foundational learning', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['purpose'], 'value' => 'Core instructional', 'parent_id' => null, 'description' => 'Core curriculum and textbook materials', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['purpose'], 'value' => 'Knowledge expansion', 'parent_id' => null, 'description' => 'Supplemental content readers and expanded learning', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['purpose'], 'value' => 'Literacy development', 'parent_id' => null, 'description' => 'Reading and writing skill development', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // ============================================================
            // GENRE VALUES
            // ============================================================
            ['classification_type_id' => $typeIds['genre'], 'value' => 'Basic numeracy', 'parent_id' => null, 'description' => 'Early mathematics and counting', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['genre'], 'value' => 'Content readers', 'parent_id' => null, 'description' => 'Subject-specific informational texts', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['genre'], 'value' => 'Narrative readers', 'parent_id' => null, 'description' => 'Story-based reading materials', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['genre'], 'value' => 'Textbook readers', 'parent_id' => null, 'description' => 'Structured educational textbooks', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // ============================================================
            // SUB-GENRE VALUES
            // ============================================================
            ['classification_type_id' => $typeIds['subgenre'], 'value' => 'Developing and fluent readers', 'parent_id' => null, 'description' => 'Intermediate reading level', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['subgenre'], 'value' => 'Emerging and early readers', 'parent_id' => null, 'description' => 'Beginning reading level', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['subgenre'], 'value' => 'Language arts', 'parent_id' => null, 'description' => 'Language instruction materials', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['subgenre'], 'value' => 'Number recognition', 'parent_id' => null, 'description' => 'Early mathematics - numbers and counting', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['subgenre'], 'value' => 'Proficient and critical readers', 'parent_id' => null, 'description' => 'Advanced reading level', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['subgenre'], 'value' => 'Science', 'parent_id' => null, 'description' => 'Science content materials', 'sort_order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // ============================================================
            // TYPE VALUES
            // ============================================================
            ['classification_type_id' => $typeIds['type'], 'value' => 'Animal story', 'parent_id' => null, 'description' => 'Stories featuring animals', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['type'], 'value' => 'Counting', 'parent_id' => null, 'description' => 'Counting and number activities', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['type'], 'value' => 'Cultural story', 'parent_id' => null, 'description' => 'Stories about local culture and traditions', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['type'], 'value' => 'Everyday story', 'parent_id' => null, 'description' => 'Stories about daily life and experiences', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['type'], 'value' => 'Fictional story', 'parent_id' => null, 'description' => 'Imaginative fiction narratives', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['type'], 'value' => 'Folk tale', 'parent_id' => null, 'description' => 'Traditional folklore and legends', 'sort_order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['type'], 'value' => 'Life sciences and environment', 'parent_id' => null, 'description' => 'Biology, ecology, and environmental science', 'sort_order' => 7, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['type'], 'value' => 'Reading program', 'parent_id' => null, 'description' => 'Structured reading curriculum materials', 'sort_order' => 8, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['type'], 'value' => 'Science', 'parent_id' => null, 'description' => 'General science content', 'sort_order' => 9, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // ============================================================
            // THEMES/USES VALUES
            // ============================================================
            // Note: No themes/uses values found in the CSV data

            // ============================================================
            // LEARNER LEVEL VALUES
            // ============================================================
            ['classification_type_id' => $typeIds['learner-level'], 'value' => 'Grade 2', 'parent_id' => null, 'description' => '2nd grade level', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => $typeIds['learner-level'], 'value' => 'Grade 7', 'parent_id' => null, 'description' => '7th grade level', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('classification_values')->insert($values);
    }
}
