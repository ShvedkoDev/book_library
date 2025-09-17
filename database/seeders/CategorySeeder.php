<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert parent categories first
        $parentCategories = [
            ['id' => 1, 'name' => 'Mathematics', 'slug' => 'mathematics', 'description' => 'Mathematics and numerical concepts', 'parent_id' => null, 'sort_order' => 1],
            ['id' => 2, 'name' => 'Science', 'slug' => 'science', 'description' => 'Natural sciences and scientific concepts', 'parent_id' => null, 'sort_order' => 2],
            ['id' => 3, 'name' => 'Language Arts', 'slug' => 'language-arts', 'description' => 'Reading, writing, and language skills', 'parent_id' => null, 'sort_order' => 3],
            ['id' => 4, 'name' => 'Social Studies', 'slug' => 'social-studies', 'description' => 'History, geography, and social sciences', 'parent_id' => null, 'sort_order' => 4],
            ['id' => 5, 'name' => 'Cultural Studies', 'slug' => 'cultural-studies', 'description' => 'Micronesian culture and traditions', 'parent_id' => null, 'sort_order' => 5],
            ['id' => 6, 'name' => 'Grade Level', 'slug' => 'grade-level', 'description' => 'Educational level classification', 'parent_id' => null, 'sort_order' => 6],
            ['id' => 7, 'name' => 'Resource Type', 'slug' => 'resource-type', 'description' => 'Type of educational resource', 'parent_id' => null, 'sort_order' => 7],
        ];

        foreach ($parentCategories as $category) {
            DB::table('categories')->insert(array_merge($category, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Insert subcategories
        $subCategories = [
            // Mathematics subcategories
            ['name' => 'Arithmetic', 'slug' => 'arithmetic', 'description' => 'Basic arithmetic operations', 'parent_id' => 1, 'sort_order' => 1],
            ['name' => 'Geometry', 'slug' => 'geometry', 'description' => 'Shapes, measurements, and spatial concepts', 'parent_id' => 1, 'sort_order' => 2],
            ['name' => 'Algebra', 'slug' => 'algebra', 'description' => 'Basic algebraic concepts', 'parent_id' => 1, 'sort_order' => 3],
            ['name' => 'Statistics', 'slug' => 'statistics', 'description' => 'Data analysis and probability', 'parent_id' => 1, 'sort_order' => 4],

            // Science subcategories
            ['name' => 'Biology', 'slug' => 'biology', 'description' => 'Living organisms and life processes', 'parent_id' => 2, 'sort_order' => 1],
            ['name' => 'Environmental Science', 'slug' => 'environmental-science', 'description' => 'Environment and ecosystem studies', 'parent_id' => 2, 'sort_order' => 2],
            ['name' => 'Marine Science', 'slug' => 'marine-science', 'description' => 'Ocean and marine life studies', 'parent_id' => 2, 'sort_order' => 3],
            ['name' => 'Physics', 'slug' => 'physics', 'description' => 'Physical sciences and phenomena', 'parent_id' => 2, 'sort_order' => 4],
            ['name' => 'Chemistry', 'slug' => 'chemistry', 'description' => 'Chemical processes and substances', 'parent_id' => 2, 'sort_order' => 5],

            // Language Arts subcategories
            ['name' => 'Reading', 'slug' => 'reading', 'description' => 'Reading comprehension and skills', 'parent_id' => 3, 'sort_order' => 1],
            ['name' => 'Writing', 'slug' => 'writing', 'description' => 'Writing skills and composition', 'parent_id' => 3, 'sort_order' => 2],
            ['name' => 'Literature', 'slug' => 'literature', 'description' => 'Literary works and analysis', 'parent_id' => 3, 'sort_order' => 3],
            ['name' => 'Vocabulary', 'slug' => 'vocabulary', 'description' => 'Word knowledge and language development', 'parent_id' => 3, 'sort_order' => 4],

            // Social Studies subcategories
            ['name' => 'History', 'slug' => 'history', 'description' => 'Historical events and periods', 'parent_id' => 4, 'sort_order' => 1],
            ['name' => 'Geography', 'slug' => 'geography', 'description' => 'Physical and human geography', 'parent_id' => 4, 'sort_order' => 2],
            ['name' => 'Civics', 'slug' => 'civics', 'description' => 'Government and citizenship', 'parent_id' => 4, 'sort_order' => 3],
            ['name' => 'Economics', 'slug' => 'economics', 'description' => 'Economic principles and concepts', 'parent_id' => 4, 'sort_order' => 4],

            // Cultural Studies subcategories
            ['name' => 'Traditional Stories', 'slug' => 'traditional-stories', 'description' => 'Micronesian folktales and legends', 'parent_id' => 5, 'sort_order' => 1],
            ['name' => 'Language Preservation', 'slug' => 'language-preservation', 'description' => 'Native language materials', 'parent_id' => 5, 'sort_order' => 2],
            ['name' => 'Traditional Practices', 'slug' => 'traditional-practices', 'description' => 'Cultural practices and customs', 'parent_id' => 5, 'sort_order' => 3],
            ['name' => 'Island History', 'slug' => 'island-history', 'description' => 'Local and regional history', 'parent_id' => 5, 'sort_order' => 4],

            // Grade Level subcategories
            ['name' => 'Pre-K', 'slug' => 'pre-k', 'description' => 'Pre-Kindergarten (Ages 3-4)', 'parent_id' => 6, 'sort_order' => 1],
            ['name' => 'Kindergarten', 'slug' => 'kindergarten', 'description' => 'Kindergarten (Ages 5-6)', 'parent_id' => 6, 'sort_order' => 2],
            ['name' => 'Elementary (1-3)', 'slug' => 'elementary-early', 'description' => 'Early Elementary (Grades 1-3)', 'parent_id' => 6, 'sort_order' => 3],
            ['name' => 'Elementary (4-6)', 'slug' => 'elementary-late', 'description' => 'Late Elementary (Grades 4-6)', 'parent_id' => 6, 'sort_order' => 4],
            ['name' => 'Middle School', 'slug' => 'middle-school', 'description' => 'Middle School (Grades 7-8)', 'parent_id' => 6, 'sort_order' => 5],
            ['name' => 'High School', 'slug' => 'high-school', 'description' => 'High School (Grades 9-12)', 'parent_id' => 6, 'sort_order' => 6],

            // Resource Type subcategories
            ['name' => 'Textbook', 'slug' => 'textbook', 'description' => 'Educational textbooks', 'parent_id' => 7, 'sort_order' => 1],
            ['name' => 'Workbook', 'slug' => 'workbook', 'description' => 'Practice workbooks and exercises', 'parent_id' => 7, 'sort_order' => 2],
            ['name' => 'Teacher Guide', 'slug' => 'teacher-guide', 'description' => 'Instructional guides for teachers', 'parent_id' => 7, 'sort_order' => 3],
            ['name' => 'Assessment', 'slug' => 'assessment', 'description' => 'Tests and evaluation materials', 'parent_id' => 7, 'sort_order' => 4],
            ['name' => 'Reference', 'slug' => 'reference', 'description' => 'Reference materials and dictionaries', 'parent_id' => 7, 'sort_order' => 5],
        ];

        foreach ($subCategories as $category) {
            DB::table('categories')->insert(array_merge($category, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
