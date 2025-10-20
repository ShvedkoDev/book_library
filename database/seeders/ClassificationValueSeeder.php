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
        $values = [
            // Purpose values
            ['classification_type_id' => 1, 'value' => 'Core Curriculum', 'parent_id' => null, 'description' => 'Essential curriculum materials', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 1, 'value' => 'Supplemental', 'parent_id' => null, 'description' => 'Additional support materials', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 1, 'value' => 'Reference', 'parent_id' => null, 'description' => 'Reference and research materials', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 1, 'value' => 'Professional Development', 'parent_id' => null, 'description' => 'Teacher training resources', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Genre values
            ['classification_type_id' => 2, 'value' => 'Fiction', 'parent_id' => null, 'description' => 'Fictional narratives', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 2, 'value' => 'Non-Fiction', 'parent_id' => null, 'description' => 'Factual and informational content', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 2, 'value' => 'Poetry', 'parent_id' => null, 'description' => 'Poetic works', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 2, 'value' => 'Traditional Stories', 'parent_id' => null, 'description' => 'Folklore and oral traditions', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Type values
            ['classification_type_id' => 3, 'value' => 'Textbook', 'parent_id' => null, 'description' => 'Structured educational textbook', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 3, 'value' => 'Workbook', 'parent_id' => null, 'description' => 'Student activity workbook', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 3, 'value' => 'Readers', 'parent_id' => null, 'description' => 'Reading materials', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 3, 'value' => 'Teacher Guide', 'parent_id' => null, 'description' => 'Instructional guide for educators', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 3, 'value' => 'Assessment Materials', 'parent_id' => null, 'description' => 'Testing and evaluation resources', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Themes/Uses values
            ['classification_type_id' => 4, 'value' => 'Language Arts', 'parent_id' => null, 'description' => 'Reading, writing, language skills', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 4, 'value' => 'Mathematics', 'parent_id' => null, 'description' => 'Mathematical concepts and skills', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 4, 'value' => 'Science', 'parent_id' => null, 'description' => 'Scientific concepts and inquiry', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 4, 'value' => 'Social Studies', 'parent_id' => null, 'description' => 'History, geography, culture', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 4, 'value' => 'Cultural Studies', 'parent_id' => null, 'description' => 'Traditional culture and practices', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 4, 'value' => 'Language Preservation', 'parent_id' => null, 'description' => 'Indigenous language materials', 'sort_order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Learner Level values
            ['classification_type_id' => 5, 'value' => 'Preschool', 'parent_id' => null, 'description' => 'Ages 3-5', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 5, 'value' => 'Elementary (K-5)', 'parent_id' => null, 'description' => 'Kindergarten through 5th grade', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 5, 'value' => 'Middle School (6-8)', 'parent_id' => null, 'description' => '6th through 8th grade', 'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 5, 'value' => 'High School (9-12)', 'parent_id' => null, 'description' => '9th through 12th grade', 'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 5, 'value' => 'Adult Education', 'parent_id' => null, 'description' => 'Adult learners', 'sort_order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['classification_type_id' => 5, 'value' => 'Higher Education', 'parent_id' => null, 'description' => 'College and university level', 'sort_order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('classification_values')->insert($values);
    }
}
