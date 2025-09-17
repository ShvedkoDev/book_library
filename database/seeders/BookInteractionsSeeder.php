<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookInteractionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedBookAuthors();
        $this->seedBookCategories();
        $this->seedBookEditions();
        $this->seedBookRatings();
        $this->seedBookReviews();
        $this->seedBookBookmarks();
        $this->seedBookKeywords();
        $this->seedBookIdentifiers();
    }

    private function seedBookAuthors(): void
    {
        $bookAuthors = [
            // Mathematics books
            ['book_id' => 1, 'author_id' => 3, 'role' => 'author', 'sort_order' => 1], // Anna Nakamura - Pacific Islands Mathematics
            ['book_id' => 2, 'author_id' => 4, 'role' => 'author', 'sort_order' => 1], // Elder Joseph - Geometry Through Navigation
            ['book_id' => 3, 'author_id' => 18, 'role' => 'author', 'sort_order' => 1], // Margaret Sigrah - Chuukese Arithmetic

            // Science books
            ['book_id' => 4, 'author_id' => 5, 'role' => 'author', 'sort_order' => 1], // Dr. Sarah Williams - Coral Reefs
            ['book_id' => 5, 'author_id' => 19, 'role' => 'author', 'sort_order' => 1], // Dr. Kevin O'Brien - Climate Change

            // Language Arts
            ['book_id' => 6, 'author_id' => 4, 'role' => 'author', 'sort_order' => 1], // Elder Joseph - Traditional Stories
            ['book_id' => 7, 'author_id' => 9, 'role' => 'author', 'sort_order' => 1], // Leilani Kihleng - Pohnpeian Primer

            // Social Studies
            ['book_id' => 8, 'author_id' => 6, 'role' => 'author', 'sort_order' => 1], // Peter Segal - FSM History
            ['book_id' => 9, 'author_id' => 17, 'role' => 'author', 'sort_order' => 1], // Dr. David Martinez - Geography

            // Cultural Studies
            ['book_id' => 10, 'author_id' => 12, 'role' => 'author', 'sort_order' => 1], // Francis Toribiong - Navigation
            ['book_id' => 11, 'author_id' => 16, 'role' => 'author', 'sort_order' => 1], // Carmen Bigler - Stone Money
            ['book_id' => 12, 'author_id' => 18, 'role' => 'author', 'sort_order' => 1], // Margaret Sigrah - Kosraean Dict

            // More assignments for variety
            ['book_id' => 13, 'author_id' => 5, 'role' => 'author', 'sort_order' => 1], // Marine Biology
            ['book_id' => 14, 'author_id' => 8, 'role' => 'author', 'sort_order' => 1], // Elementary Physics
            ['book_id' => 15, 'author_id' => 20, 'role' => 'author', 'sort_order' => 1], // Marshallese Stories
            ['book_id' => 16, 'author_id' => 15, 'role' => 'author', 'sort_order' => 1], // Basic Chemistry
            ['book_id' => 17, 'author_id' => 16, 'role' => 'author', 'sort_order' => 1], // Palauan Culture
            ['book_id' => 18, 'author_id' => 13, 'role' => 'author', 'sort_order' => 1], // Pacific Economics
            ['book_id' => 19, 'author_id' => 2, 'role' => 'author', 'sort_order' => 1], // Carolinian Basics
            ['book_id' => 20, 'author_id' => 6, 'role' => 'author', 'sort_order' => 1], // WWII Pacific

            // Continue with remaining books
            ['book_id' => 21, 'author_id' => 7, 'role' => 'author', 'sort_order' => 1], // Chamorro Folklore
            ['book_id' => 22, 'author_id' => 19, 'role' => 'author', 'sort_order' => 1], // Environmental Conservation
            ['book_id' => 23, 'author_id' => 3, 'role' => 'author', 'sort_order' => 1], // Statistics
            ['book_id' => 24, 'author_id' => 20, 'role' => 'author', 'sort_order' => 1], // Traditional Fishing
            ['book_id' => 25, 'author_id' => 13, 'role' => 'author', 'sort_order' => 1], // Government Civics
            ['book_id' => 26, 'author_id' => 16, 'role' => 'author', 'sort_order' => 1], // Pacific Art
            ['book_id' => 27, 'author_id' => 2, 'role' => 'author', 'sort_order' => 1], // Ulithian Guide
            ['book_id' => 28, 'author_id' => 3, 'role' => 'author', 'sort_order' => 1], // Algebra
            ['book_id' => 29, 'author_id' => 11, 'role' => 'author', 'sort_order' => 1], // Reading Comprehension
            ['book_id' => 30, 'author_id' => 8, 'role' => 'author', 'sort_order' => 1], // Geology

            // Teacher resources and remaining books
            ['book_id' => 31, 'author_id' => 1, 'role' => 'author', 'sort_order' => 1], // Teacher Guide
            ['book_id' => 32, 'author_id' => 11, 'role' => 'author', 'sort_order' => 1], // Assessment
            ['book_id' => 33, 'author_id' => 16, 'role' => 'author', 'sort_order' => 1], // Music Dance
            ['book_id' => 34, 'author_id' => 11, 'role' => 'author', 'sort_order' => 1], // Writing Skills
            ['book_id' => 35, 'author_id' => 19, 'role' => 'author', 'sort_order' => 1], // Climate Science
            ['book_id' => 36, 'author_id' => 10, 'role' => 'author', 'sort_order' => 1], // Literature Anthology
            ['book_id' => 37, 'author_id' => 1, 'role' => 'author', 'sort_order' => 1], // Health Education
            ['book_id' => 38, 'author_id' => 17, 'role' => 'author', 'sort_order' => 1], // Maps Geography
            ['book_id' => 39, 'author_id' => 11, 'role' => 'author', 'sort_order' => 1], // Vocabulary
            ['book_id' => 40, 'author_id' => 12, 'role' => 'author', 'sort_order' => 1], // Pacific Astronomy

            // Final 10 books
            ['book_id' => 41, 'author_id' => 13, 'role' => 'author', 'sort_order' => 1], // Critical Thinking
            ['book_id' => 42, 'author_id' => 19, 'role' => 'author', 'sort_order' => 1], // Sustainable Agriculture
            ['book_id' => 43, 'author_id' => 8, 'role' => 'author', 'sort_order' => 1], // Computer Literacy
            ['book_id' => 44, 'author_id' => 5, 'role' => 'author', 'sort_order' => 1], // Pacific Plant Life
            ['book_id' => 45, 'author_id' => 1, 'role' => 'author', 'sort_order' => 1], // Community Development
            ['book_id' => 46, 'author_id' => 19, 'role' => 'author', 'sort_order' => 1], // Weather Patterns
            ['book_id' => 47, 'author_id' => 1, 'role' => 'author', 'sort_order' => 1], // Early Childhood
            ['book_id' => 48, 'author_id' => 12, 'role' => 'author', 'sort_order' => 1], // Pacific Philosophy
            ['book_id' => 49, 'author_id' => 13, 'role' => 'author', 'sort_order' => 1], // Emergency Prep
            ['book_id' => 50, 'author_id' => 14, 'role' => 'author', 'sort_order' => 1], // Pacific Leadership
        ];

        foreach ($bookAuthors as $bookAuthor) {
            DB::table('book_authors')->insert(array_merge($bookAuthor, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedBookCategories(): void
    {
        $bookCategories = [
            // Mathematics books (Parent: Mathematics = 1)
            ['book_id' => 1, 'category_id' => 8, 'is_primary' => true], // Arithmetic
            ['book_id' => 1, 'category_id' => 24, 'is_primary' => false], // Elementary 1-3
            ['book_id' => 2, 'category_id' => 9, 'is_primary' => true], // Geometry
            ['book_id' => 2, 'category_id' => 25, 'is_primary' => false], // Elementary 4-6
            ['book_id' => 3, 'category_id' => 8, 'is_primary' => true], // Arithmetic
            ['book_id' => 3, 'category_id' => 19, 'is_primary' => false], // Language Preservation
            ['book_id' => 23, 'category_id' => 11, 'is_primary' => true], // Statistics
            ['book_id' => 28, 'category_id' => 10, 'is_primary' => true], // Algebra

            // Science books (Parent: Science = 2)
            ['book_id' => 4, 'category_id' => 12, 'is_primary' => true], // Biology
            ['book_id' => 4, 'category_id' => 14, 'is_primary' => false], // Marine Science
            ['book_id' => 5, 'category_id' => 13, 'is_primary' => true], // Environmental Science
            ['book_id' => 13, 'category_id' => 14, 'is_primary' => true], // Marine Science
            ['book_id' => 14, 'category_id' => 15, 'is_primary' => true], // Physics
            ['book_id' => 16, 'category_id' => 16, 'is_primary' => true], // Chemistry
            ['book_id' => 30, 'category_id' => 15, 'is_primary' => true], // Physics (Geology)
            ['book_id' => 35, 'category_id' => 13, 'is_primary' => true], // Environmental Science
            ['book_id' => 44, 'category_id' => 12, 'is_primary' => true], // Biology (Plant Life)
            ['book_id' => 46, 'category_id' => 13, 'is_primary' => true], // Environmental Science

            // Language Arts books (Parent: Language Arts = 3)
            ['book_id' => 6, 'category_id' => 19, 'is_primary' => true], // Literature
            ['book_id' => 7, 'category_id' => 17, 'is_primary' => true], // Reading
            ['book_id' => 12, 'category_id' => 20, 'is_primary' => true], // Vocabulary
            ['book_id' => 15, 'category_id' => 18, 'is_primary' => true], // Traditional Stories
            ['book_id' => 19, 'category_id' => 19, 'is_primary' => true], // Language Preservation
            ['book_id' => 21, 'category_id' => 18, 'is_primary' => true], // Traditional Stories
            ['book_id' => 27, 'category_id' => 19, 'is_primary' => true], // Language Preservation
            ['book_id' => 29, 'category_id' => 17, 'is_primary' => true], // Reading
            ['book_id' => 34, 'category_id' => 18, 'is_primary' => true], // Writing
            ['book_id' => 36, 'category_id' => 19, 'is_primary' => true], // Literature
            ['book_id' => 39, 'category_id' => 20, 'is_primary' => true], // Vocabulary

            // Social Studies books (Parent: Social Studies = 4)
            ['book_id' => 8, 'category_id' => 21, 'is_primary' => true], // History
            ['book_id' => 9, 'category_id' => 22, 'is_primary' => true], // Geography
            ['book_id' => 18, 'category_id' => 24, 'is_primary' => true], // Economics
            ['book_id' => 20, 'category_id' => 21, 'is_primary' => true], // History
            ['book_id' => 25, 'category_id' => 23, 'is_primary' => true], // Civics
            ['book_id' => 38, 'category_id' => 22, 'is_primary' => true], // Geography

            // Cultural Studies books (Parent: Cultural Studies = 5)
            ['book_id' => 10, 'category_id' => 20, 'is_primary' => true], // Traditional Practices
            ['book_id' => 11, 'category_id' => 20, 'is_primary' => true], // Traditional Practices
            ['book_id' => 17, 'category_id' => 20, 'is_primary' => true], // Traditional Practices
            ['book_id' => 24, 'category_id' => 20, 'is_primary' => true], // Traditional Practices
            ['book_id' => 26, 'category_id' => 20, 'is_primary' => true], // Traditional Practices
            ['book_id' => 33, 'category_id' => 20, 'is_primary' => true], // Traditional Practices
            ['book_id' => 40, 'category_id' => 20, 'is_primary' => true], // Traditional Practices
            ['book_id' => 48, 'category_id' => 20, 'is_primary' => true], // Traditional Practices

            // Resource Types
            ['book_id' => 31, 'category_id' => 30, 'is_primary' => false], // Teacher Guide
            ['book_id' => 32, 'category_id' => 31, 'is_primary' => false], // Assessment
            ['book_id' => 12, 'category_id' => 32, 'is_primary' => false], // Reference
            ['book_id' => 3, 'category_id' => 29, 'is_primary' => false], // Workbook
            ['book_id' => 34, 'category_id' => 29, 'is_primary' => false], // Workbook

            // Grade levels
            ['book_id' => 1, 'category_id' => 24, 'is_primary' => false], // Elementary 1-3
            ['book_id' => 7, 'category_id' => 24, 'is_primary' => false], // Elementary 1-3
            ['book_id' => 14, 'category_id' => 26, 'is_primary' => false], // Middle School
            ['book_id' => 28, 'category_id' => 27, 'is_primary' => false], // High School
            ['book_id' => 47, 'category_id' => 22, 'is_primary' => false], // Pre-K
        ];

        foreach ($bookCategories as $bookCategory) {
            DB::table('book_categories')->insert(array_merge($bookCategory, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedBookEditions(): void
    {
        $bookEditions = [
            // Some books have multiple editions
            ['parent_book_id' => 1, 'edition_book_id' => 1, 'edition_type' => 'revised', 'notes' => 'Updated with new Pacific contexts'],
            ['parent_book_id' => 4, 'edition_book_id' => 4, 'edition_type' => 'updated', 'notes' => 'Added recent coral research'],
            ['parent_book_id' => 8, 'edition_book_id' => 8, 'edition_type' => 'revised', 'notes' => 'Updated to include recent political developments'],
            ['parent_book_id' => 12, 'edition_book_id' => 12, 'edition_type' => 'updated', 'notes' => 'Expanded vocabulary entries'],
        ];

        foreach ($bookEditions as $edition) {
            DB::table('book_editions')->insert(array_merge($edition, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedBookRatings(): void
    {
        $ratings = [
            // Popular books get more ratings
            ['book_id' => 1, 'user_id' => 2, 'rating' => 5],
            ['book_id' => 1, 'user_id' => 3, 'rating' => 4],
            ['book_id' => 1, 'user_id' => 4, 'rating' => 5],
            ['book_id' => 4, 'user_id' => 2, 'rating' => 5],
            ['book_id' => 4, 'user_id' => 3, 'rating' => 5],
            ['book_id' => 4, 'user_id' => 4, 'rating' => 4],
            ['book_id' => 4, 'user_id' => 5, 'rating' => 5],
            ['book_id' => 6, 'user_id' => 2, 'rating' => 4],
            ['book_id' => 6, 'user_id' => 3, 'rating' => 5],
            ['book_id' => 6, 'user_id' => 5, 'rating' => 4],
            ['book_id' => 8, 'user_id' => 3, 'rating' => 5],
            ['book_id' => 8, 'user_id' => 4, 'rating' => 4],
            ['book_id' => 8, 'user_id' => 5, 'rating' => 5],
            ['book_id' => 10, 'user_id' => 2, 'rating' => 5],
            ['book_id' => 10, 'user_id' => 4, 'rating' => 4],
            ['book_id' => 12, 'user_id' => 2, 'rating' => 5],
            ['book_id' => 12, 'user_id' => 3, 'rating' => 5],
            ['book_id' => 15, 'user_id' => 3, 'rating' => 4],
            ['book_id' => 15, 'user_id' => 5, 'rating' => 5],
            ['book_id' => 20, 'user_id' => 2, 'rating' => 4],
            ['book_id' => 20, 'user_id' => 4, 'rating' => 5],
            ['book_id' => 22, 'user_id' => 3, 'rating' => 5],
            ['book_id' => 22, 'user_id' => 5, 'rating' => 4],
            ['book_id' => 31, 'user_id' => 2, 'rating' => 5],
            ['book_id' => 31, 'user_id' => 3, 'rating' => 4],
            ['book_id' => 31, 'user_id' => 4, 'rating' => 5],
            ['book_id' => 35, 'user_id' => 3, 'rating' => 5],
            ['book_id' => 40, 'user_id' => 2, 'rating' => 4],
            ['book_id' => 47, 'user_id' => 4, 'rating' => 5],
            ['book_id' => 50, 'user_id' => 5, 'rating' => 4],
        ];

        foreach ($ratings as $rating) {
            DB::table('book_ratings')->insert(array_merge($rating, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedBookReviews(): void
    {
        $reviews = [
            [
                'book_id' => 1,
                'user_id' => 2,
                'review_text' => 'Excellent math textbook that really connects to our students\' island experiences. My third graders love the problems about counting coconuts and calculating fishing boat distances.',
                'is_approved' => true,
                'approved_by' => 1,
                'approved_at' => now()->subDays(5),
            ],
            [
                'book_id' => 4,
                'user_id' => 3,
                'review_text' => 'As a science teacher, this coral reef guide is invaluable. The photographs are stunning and the content is scientifically accurate while being accessible to students.',
                'is_approved' => true,
                'approved_by' => 1,
                'approved_at' => now()->subDays(10),
            ],
            [
                'book_id' => 6,
                'user_id' => 5,
                'review_text' => 'These traditional stories are beautifully told and help preserve our cultural heritage. Perfect for storytime with elementary students.',
                'is_approved' => true,
                'approved_by' => 1,
                'approved_at' => now()->subDays(15),
            ],
            [
                'book_id' => 8,
                'user_id' => 4,
                'review_text' => 'Comprehensive historical account that helps students understand their heritage. Well-researched and engaging.',
                'is_approved' => true,
                'approved_by' => 1,
                'approved_at' => now()->subDays(8),
            ],
            [
                'book_id' => 12,
                'user_id' => 3,
                'review_text' => 'Essential resource for Kosraean language preservation. The bilingual format makes it accessible to learners at different levels.',
                'is_approved' => true,
                'approved_by' => 1,
                'approved_at' => now()->subDays(12),
            ],
            [
                'book_id' => 31,
                'user_id' => 2,
                'review_text' => 'Outstanding professional development resource. The culturally responsive teaching strategies are practical and effective.',
                'is_approved' => true,
                'approved_by' => 1,
                'approved_at' => now()->subDays(3),
            ],
        ];

        foreach ($reviews as $review) {
            DB::table('book_reviews')->insert(array_merge($review, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedBookBookmarks(): void
    {
        $bookmarks = [
            ['book_id' => 1, 'user_id' => 2],
            ['book_id' => 4, 'user_id' => 2],
            ['book_id' => 6, 'user_id' => 2],
            ['book_id' => 31, 'user_id' => 2],
            ['book_id' => 1, 'user_id' => 3],
            ['book_id' => 8, 'user_id' => 3],
            ['book_id' => 12, 'user_id' => 3],
            ['book_id' => 4, 'user_id' => 4],
            ['book_id' => 10, 'user_id' => 4],
            ['book_id' => 20, 'user_id' => 4],
            ['book_id' => 15, 'user_id' => 5],
            ['book_id' => 22, 'user_id' => 5],
            ['book_id' => 50, 'user_id' => 5],
        ];

        foreach ($bookmarks as $bookmark) {
            DB::table('book_bookmarks')->insert(array_merge($bookmark, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedBookKeywords(): void
    {
        $keywords = [
            // Mathematics keywords
            ['book_id' => 1, 'keyword' => 'elementary math'],
            ['book_id' => 1, 'keyword' => 'Pacific context'],
            ['book_id' => 1, 'keyword' => 'problem solving'],
            ['book_id' => 2, 'keyword' => 'navigation'],
            ['book_id' => 2, 'keyword' => 'traditional knowledge'],
            ['book_id' => 2, 'keyword' => 'geometry'],
            ['book_id' => 28, 'keyword' => 'algebra'],
            ['book_id' => 28, 'keyword' => 'high school'],

            // Science keywords
            ['book_id' => 4, 'keyword' => 'coral reefs'],
            ['book_id' => 4, 'keyword' => 'marine biology'],
            ['book_id' => 4, 'keyword' => 'Pacific ocean'],
            ['book_id' => 5, 'keyword' => 'climate change'],
            ['book_id' => 5, 'keyword' => 'environmental'],
            ['book_id' => 22, 'keyword' => 'conservation'],
            ['book_id' => 22, 'keyword' => 'sustainability'],

            // Cultural keywords
            ['book_id' => 6, 'keyword' => 'folktales'],
            ['book_id' => 6, 'keyword' => 'oral tradition'],
            ['book_id' => 6, 'keyword' => 'cultural heritage'],
            ['book_id' => 10, 'keyword' => 'navigation'],
            ['book_id' => 10, 'keyword' => 'wayfinding'],
            ['book_id' => 11, 'keyword' => 'stone money'],
            ['book_id' => 11, 'keyword' => 'traditional economics'],

            // Language keywords
            ['book_id' => 7, 'keyword' => 'Pohnpeian language'],
            ['book_id' => 12, 'keyword' => 'Kosraean dictionary'],
            ['book_id' => 15, 'keyword' => 'Marshallese stories'],
            ['book_id' => 19, 'keyword' => 'Carolinian language'],
            ['book_id' => 27, 'keyword' => 'Ulithian language'],

            // Educational keywords
            ['book_id' => 31, 'keyword' => 'teacher training'],
            ['book_id' => 31, 'keyword' => 'professional development'],
            ['book_id' => 47, 'keyword' => 'early childhood'],
            ['book_id' => 47, 'keyword' => 'pre-kindergarten'],
        ];

        foreach ($keywords as $keyword) {
            DB::table('book_keywords')->insert(array_merge($keyword, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedBookIdentifiers(): void
    {
        $identifiers = [
            ['book_id' => 4, 'identifier_type' => 'doi', 'identifier_value' => '10.1234/coral.reefs.2018'],
            ['book_id' => 5, 'identifier_type' => 'doi', 'identifier_value' => '10.1234/climate.pacific.2022'],
            ['book_id' => 8, 'identifier_type' => 'oclc', 'identifier_value' => '123456789'],
            ['book_id' => 13, 'identifier_type' => 'doi', 'identifier_value' => '10.1234/marine.bio.2021'],
            ['book_id' => 20, 'identifier_type' => 'oclc', 'identifier_value' => '987654321'],
            ['book_id' => 31, 'identifier_type' => 'oclc', 'identifier_value' => '456789123'],
        ];

        foreach ($identifiers as $identifier) {
            DB::table('book_identifiers')->insert(array_merge($identifier, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}