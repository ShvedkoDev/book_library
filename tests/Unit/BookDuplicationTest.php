<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Creator;
use App\Models\Language;
use App\Models\Publisher;
use App\Models\Collection;
use App\Models\ClassificationValue;
use App\Models\GeographicLocation;
use App\Services\BookDuplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookDuplicationTest extends TestCase
{
    use RefreshDatabase;

    protected BookDuplicationService $duplicationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->duplicationService = app(BookDuplicationService::class);
    }

    /** @test */
    public function it_can_duplicate_a_basic_book()
    {
        // Arrange: Create a source book with basic fields
        $sourceBook = Book::factory()->create([
            'title' => 'Original Book Title',
            'subtitle' => 'Original Subtitle',
            'publication_year' => 2023,
            'pages' => 100,
            'description' => 'Original description',
            'view_count' => 50,
            'download_count' => 25,
        ]);

        // Act: Duplicate the book
        $duplicate = $sourceBook->duplicate();

        // Assert: Check basic duplication
        $this->assertInstanceOf(Book::class, $duplicate);
        $this->assertNotEquals($sourceBook->id, $duplicate->id);
        $this->assertNull($duplicate->title); // Title should be cleared
        $this->assertEquals(0, $duplicate->view_count); // Statistics should be reset
        $this->assertEquals(0, $duplicate->download_count);
        $this->assertEquals($sourceBook->id, $duplicate->duplicated_from_book_id);
        $this->assertNotNull($duplicate->duplicated_at);
    }

    /** @test */
    public function it_preserves_basic_fields_when_duplicating()
    {
        // Arrange
        $publisher = Publisher::factory()->create();
        $collection = Collection::factory()->create();

        $sourceBook = Book::factory()->create([
            'title' => 'Original Title',
            'subtitle' => 'Original Subtitle',
            'publication_year' => 2023,
            'pages' => 100,
            'description' => 'Test description',
            'physical_type' => 'book',
            'publisher_id' => $publisher->id,
            'collection_id' => $collection->id,
            'access_level' => 'full',
        ]);

        // Act
        $duplicate = $sourceBook->duplicate();

        // Assert: These fields should be preserved
        $this->assertEquals($sourceBook->subtitle, $duplicate->subtitle);
        $this->assertEquals($sourceBook->publication_year, $duplicate->publication_year);
        $this->assertEquals($sourceBook->pages, $duplicate->pages);
        $this->assertEquals($sourceBook->description, $duplicate->description);
        $this->assertEquals($sourceBook->physical_type, $duplicate->physical_type);
        $this->assertEquals($sourceBook->publisher_id, $duplicate->publisher_id);
        $this->assertEquals($sourceBook->collection_id, $duplicate->collection_id);
        $this->assertEquals($sourceBook->access_level, $duplicate->access_level);
    }

    /** @test */
    public function it_copies_creator_relationships()
    {
        // Arrange
        $sourceBook = Book::factory()->create(['title' => 'Original']);

        $author = Creator::factory()->create(['name' => 'John Smith']);
        $illustrator = Creator::factory()->create(['name' => 'Jane Doe']);

        $sourceBook->bookCreators()->create([
            'creator_id' => $author->id,
            'creator_type' => 'author',
            'sort_order' => 1,
        ]);

        $sourceBook->bookCreators()->create([
            'creator_id' => $illustrator->id,
            'creator_type' => 'illustrator',
            'sort_order' => 2,
        ]);

        // Act
        $duplicate = $sourceBook->duplicate();

        // Assert
        $this->assertEquals(2, $duplicate->bookCreators()->count());

        $duplicateAuthors = $duplicate->authors()->get();
        $this->assertCount(1, $duplicateAuthors);
        $this->assertEquals('John Smith', $duplicateAuthors->first()->name);

        $duplicateIllustrators = $duplicate->illustrators()->get();
        $this->assertCount(1, $duplicateIllustrators);
        $this->assertEquals('Jane Doe', $duplicateIllustrators->first()->name);
    }

    /** @test */
    public function it_copies_language_relationships()
    {
        // Arrange
        $sourceBook = Book::factory()->create(['title' => 'Original']);

        $chuukese = Language::factory()->create(['name' => 'Chuukese']);
        $english = Language::factory()->create(['name' => 'English']);

        $sourceBook->bookLanguages()->create([
            'language_id' => $chuukese->id,
            'is_primary' => true,
        ]);

        $sourceBook->bookLanguages()->create([
            'language_id' => $english->id,
            'is_primary' => false,
        ]);

        // Act
        $duplicate = $sourceBook->duplicate();

        // Assert
        $this->assertEquals(2, $duplicate->languages()->count());

        $primaryLanguage = $duplicate->bookLanguages()
            ->where('is_primary', true)
            ->first();

        $this->assertNotNull($primaryLanguage);
        $this->assertEquals($chuukese->id, $primaryLanguage->language_id);
    }

    /** @test */
    public function it_does_not_copy_files_by_default()
    {
        // Arrange
        $sourceBook = Book::factory()->create(['title' => 'Original']);

        $sourceBook->files()->create([
            'file_type' => 'pdf',
            'file_path' => 'books/original.pdf',
            'is_primary' => true,
        ]);

        $sourceBook->files()->create([
            'file_type' => 'thumbnail',
            'file_path' => 'books/original-thumb.jpg',
            'is_primary' => true,
        ]);

        // Act
        $duplicate = $sourceBook->duplicate();

        // Assert: Files should NOT be copied by default
        $this->assertEquals(0, $duplicate->files()->count());
    }

    /** @test */
    public function it_can_copy_files_when_explicitly_requested()
    {
        // Arrange
        $sourceBook = Book::factory()->create(['title' => 'Original']);

        $sourceBook->files()->create([
            'file_type' => 'pdf',
            'file_path' => 'books/original.pdf',
            'is_primary' => true,
        ]);

        // Act: Request file copying
        $duplicate = $sourceBook->duplicate(['copy_files' => true]);

        // Assert: Files should be copied when requested
        $this->assertEquals(1, $duplicate->files()->count());
        $this->assertEquals('books/original.pdf', $duplicate->files()->first()->file_path);
    }

    /** @test */
    public function it_validates_book_before_duplication()
    {
        // Arrange: Create a book without required language
        $sourceBook = Book::factory()->create(['title' => 'Original']);
        // No languages added

        // Act
        $validation = $sourceBook->canBeDuplicated();

        // Assert
        $this->assertFalse($validation['valid']);
        $this->assertContains('Book must have at least one language', $validation['errors']);
    }

    /** @test */
    public function it_warns_when_duplicating_an_existing_duplicate()
    {
        // Arrange: Create a duplicate chain
        $original = Book::factory()->create(['title' => 'Original']);
        $firstDuplicate = $original->duplicate(['clear_title' => false, 'append_copy_suffix' => true]);

        // Act: Validate duplicating a duplicate
        $validation = $firstDuplicate->canBeDuplicated();

        // Assert
        $this->assertFalse($validation['valid']);
        $this->assertStringContainsString('already a duplicate', $validation['errors'][0]);
    }

    /** @test */
    public function it_tracks_duplication_relationships()
    {
        // Arrange
        $sourceBook = Book::factory()->create(['title' => 'Original']);

        // Act: Create multiple duplicates
        $duplicate1 = $sourceBook->duplicate(['clear_title' => false, 'append_copy_suffix' => true]);
        $duplicate2 = $sourceBook->duplicate(['clear_title' => false, 'append_copy_suffix' => true]);

        // Assert
        $this->assertTrue($sourceBook->hasBeenDuplicated());
        $this->assertEquals(2, $sourceBook->getDuplicateCount());

        $this->assertTrue($duplicate1->isDuplicate());
        $this->assertEquals($sourceBook->id, $duplicate1->duplicated_from_book_id);

        $duplicates = $sourceBook->duplicates()->get();
        $this->assertCount(2, $duplicates);
    }

    /** @test */
    public function it_finds_original_source_in_duplication_chain()
    {
        // Arrange: Create a chain (this shouldn't happen, but test it)
        $original = Book::factory()->create(['title' => 'Original']);

        // Create first duplicate
        $firstDuplicate = $this->duplicationService->duplicate($original, [
            'clear_title' => false,
            'append_copy_suffix' => true,
        ]);

        // Act & Assert
        $this->assertNull($original->getOriginalSource()); // Original has no source
        $this->assertEquals($original->id, $firstDuplicate->getOriginalSource()->id);
    }

    /** @test */
    public function it_provides_duplication_statistics()
    {
        // Arrange
        $sourceBook = Book::factory()->create(['title' => 'Original Book']);
        $duplicate = $sourceBook->duplicate(['clear_title' => false, 'append_copy_suffix' => true]);

        // Act
        $sourceStats = $sourceBook->getDuplicationStats();
        $duplicateStats = $duplicate->getDuplicationStats();

        // Assert: Source book stats
        $this->assertFalse($sourceStats['is_duplicate']);
        $this->assertNull($sourceStats['duplicated_from']);
        $this->assertEquals(1, $sourceStats['times_duplicated']);

        // Assert: Duplicate stats
        $this->assertTrue($duplicateStats['is_duplicate']);
        $this->assertEquals('Original Book', $duplicateStats['duplicated_from']);
        $this->assertEquals(0, $duplicateStats['times_duplicated']);
    }

    /** @test */
    public function it_can_bulk_duplicate_multiple_books()
    {
        // Arrange
        $book1 = Book::factory()->create(['title' => 'Book 1']);
        $book2 = Book::factory()->create(['title' => 'Book 2']);
        $book3 = Book::factory()->create(['title' => 'Book 3']);

        $bookIds = [$book1->id, $book2->id, $book3->id];

        // Act
        $results = $this->duplicationService->bulkDuplicate($bookIds, [
            'clear_title' => false,
            'append_copy_suffix' => true,
        ]);

        // Assert
        $this->assertCount(3, $results['success']);
        $this->assertCount(0, $results['failed']);

        // Verify all books were duplicated
        $this->assertEquals(1, $book1->getDuplicateCount());
        $this->assertEquals(1, $book2->getDuplicateCount());
        $this->assertEquals(1, $book3->getDuplicateCount());
    }

    /** @test */
    public function it_can_query_duplicate_books_with_scopes()
    {
        // Arrange
        $original1 = Book::factory()->create(['title' => 'Original 1']);
        $original2 = Book::factory()->create(['title' => 'Original 2']);

        $duplicate1 = $original1->duplicate(['clear_title' => false, 'append_copy_suffix' => true]);
        $duplicate2 = $original2->duplicate(['clear_title' => false, 'append_copy_suffix' => true]);

        // Act & Assert: Duplicates scope
        $duplicates = Book::duplicates()->get();
        $this->assertCount(2, $duplicates);
        $this->assertTrue($duplicates->contains($duplicate1));
        $this->assertTrue($duplicates->contains($duplicate2));

        // Act & Assert: Originals scope
        $originals = Book::originals()->get();
        $this->assertCount(2, $originals);
        $this->assertTrue($originals->contains($original1));
        $this->assertTrue($originals->contains($original2));
    }

    /** @test */
    public function it_adds_review_note_to_duplicated_book()
    {
        // Arrange
        $sourceBook = Book::factory()->create([
            'title' => 'Original',
            'notes_issue' => 'Original notes',
        ]);

        // Act
        $duplicate = $sourceBook->duplicate(['mark_for_review' => true]);

        // Assert
        $this->assertStringContainsString('DUPLICATED from "Original"', $duplicate->notes_issue);
        $this->assertStringContainsString('Please review all fields', $duplicate->notes_issue);
    }

    /** @test */
    public function it_clears_unique_identifiers()
    {
        // Arrange
        $sourceBook = Book::factory()->create([
            'title' => 'Original',
            'internal_id' => 'MLEC-2023-001',
            'palm_code' => 'PALM-CHK-001',
        ]);

        // Act
        $duplicate = $sourceBook->duplicate(['clear_identifiers' => true]);

        // Assert
        $this->assertNull($duplicate->internal_id);
        $this->assertNull($duplicate->palm_code);
    }

    /** @test */
    public function it_can_append_copy_suffix_to_title()
    {
        // Arrange
        $sourceBook = Book::factory()->create(['title' => 'Original Title']);

        // Act
        $duplicate = $sourceBook->duplicate([
            'clear_title' => false,
            'append_copy_suffix' => true,
        ]);

        // Assert
        $this->assertEquals('Original Title (Copy)', $duplicate->title);
    }

    /** @test */
    public function duplicate_has_unique_slug()
    {
        // Arrange
        $sourceBook = Book::factory()->create(['title' => 'Test Book']);

        // Act
        $duplicate = $sourceBook->duplicate([
            'clear_title' => false,
            'append_copy_suffix' => true,
        ]);

        // Assert
        $this->assertNotEquals($sourceBook->slug, $duplicate->slug);
        $this->assertNotNull($duplicate->slug);
    }
}
